<?php

declare(strict_types=1);

/**
 * Align S3 photo keys to EXIF capture time (source of truth).
 *
 * - Deletes duplicate keys that share the same GOPR id + EXIF wall-clock
 *   (keeps the key closest to EXIF; prefers non bulk-download stamps).
 * - Renames remaining keys whose prefix diverges from EXIF by more than
 *   --min-delta seconds (default 60). Leaves 1–2s clock skew alone.
 * - Updates both full + thumbnail buckets, then refreshes local caches.
 *
 * Usage:
 *   php scripts/rename-photos-to-exif.php              # dry-run
 *   php scripts/rename-photos-to-exif.php --execute
 *   php scripts/rename-photos-to-exif.php --execute --min-delta=60
 */
require_once dirname(__DIR__) . '/lib/bootstrap.php';

$execute = false;
$minDelta = 60;
foreach (array_slice($argv, 1) as $arg) {
	if ($arg === '--execute') {
		$execute = true;
	} elseif (str_starts_with($arg, '--min-delta=')) {
		$minDelta = max(0, (int) substr($arg, 12));
	}
}

$cfg = pinchard_config();
/** @var \Aws\S3\S3Client $s3 */
global $s3;

$buckets = [
	$cfg['s3_bucket_full'],
	$cfg['s3_bucket_thumbnails'],
];

$exif = pinchard_exif_dates_cache_read();
if ($exif === []) {
	fwrite(STDERR, "exif-dates cache is empty. Run scripts/cache-exif-dates.php first.\n");
	exit(1);
}

/**
 * @return ?string
 */
function pinchard_rename_proposed_key(string $filename, string $wall): ?string
{
	if (!preg_match('/_(GOPR\d+\.JPG)$/i', $filename, $m)) {
		return null;
	}
	$dt = DateTime::createFromFormat('Y/m/d H:i:s', $wall);
	if (!$dt instanceof DateTime) {
		return null;
	}

	return $dt->format('Y-m-d\TH:i:s') . '.000Z_' . $m[1];
}

function pinchard_rename_key_timestamp(string $filename): ?int
{
	if (!preg_match('/^(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2})/', $filename, $m)) {
		return null;
	}
	$dt = DateTime::createFromFormat('Y-m-d\TH:i:s', $m[1]);

	return $dt instanceof DateTime ? $dt->getTimestamp() : null;
}

/** @var array<string, list<string>> $groups */
$groups = [];
foreach ($exif as $filename => $wall) {
	if (!is_string($filename) || !is_string($wall)) {
		continue;
	}
	if (!preg_match('/_(GOPR\d+\.JPG)$/i', $filename, $m)) {
		continue;
	}
	$groups[strtoupper($m[1]) . '|' . $wall][] = $filename;
}

$deletes = [];
/** @var array<string, true> $keepers */
$keepers = [];

foreach ($groups as $groupKey => $filenames) {
	if (count($filenames) === 1) {
		$keepers[$filenames[0]] = true;
		continue;
	}

	[, $wall] = explode('|', $groupKey, 2);
	$exifDt = DateTime::createFromFormat('Y/m/d H:i:s', $wall);
	$best = null;
	$bestScore = PHP_INT_MIN;

	foreach ($filenames as $filename) {
		$proposed = pinchard_rename_proposed_key($filename, $wall);
		$score = 0;
		if ($proposed === $filename) {
			$score += 1_000_000;
		}
		$kts = pinchard_rename_key_timestamp($filename);
		if ($kts !== null && $exifDt instanceof DateTime) {
			$score -= abs($kts - $exifDt->getTimestamp());
		}
		if (str_starts_with($filename, '2018-02-26T11:3') || str_starts_with($filename, '2018-02-28T16:3') || str_starts_with($filename, '2018-03-01T18:0')) {
			$score -= 500_000;
		}
		if ($score > $bestScore) {
			$bestScore = $score;
			$best = $filename;
		}
	}

	if ($best === null) {
		continue;
	}
	$keepers[$best] = true;
	foreach ($filenames as $filename) {
		if ($filename !== $best) {
			$deletes[] = $filename;
		}
	}
}

sort($deletes);

/** @var list<array{old: string, new: string, delta: int}> $renames */
$renames = [];
$deleteSet = array_fill_keys($deletes, true);

foreach ($exif as $filename => $wall) {
	if (!isset($keepers[$filename]) || !is_string($wall)) {
		continue;
	}
	$new = pinchard_rename_proposed_key($filename, $wall);
	if ($new === null || $new === $filename) {
		continue;
	}
	$kts = pinchard_rename_key_timestamp($filename);
	$exifDt = DateTime::createFromFormat('Y/m/d H:i:s', $wall);
	if ($kts === null || !$exifDt instanceof DateTime) {
		continue;
	}
	$delta = abs($kts - $exifDt->getTimestamp());
	if ($delta <= $minDelta) {
		continue;
	}
	if (isset($exif[$new]) && !isset($deleteSet[$new])) {
		fwrite(STDERR, "SKIP rename (target exists): {$filename} -> {$new}\n");
		continue;
	}
	$renames[] = ['old' => $filename, 'new' => $new, 'delta' => $delta];
}

$mode = $execute ? 'EXECUTE' : 'DRY-RUN';
fwrite(STDERR, "{$mode}: deletes=" . count($deletes) . ' renames=' . count($renames)
	. " min_delta={$minDelta}s buckets=" . implode(',', $buckets) . "\n");

foreach ($deletes as $filename) {
	fwrite(STDERR, "DELETE {$filename}\n");
}
foreach ($renames as $row) {
	$hours = round($row['delta'] / 3600, 1);
	fwrite(STDERR, "RENAME {$row['old']}\n    -> {$row['new']} (Δ {$hours}h)\n");
}

if (!$execute) {
	fwrite(STDERR, "Dry-run only. Re-run with --execute to apply.\n");
	exit(0);
}

$failed = 0;

foreach ($buckets as $bucket) {
	fwrite(STDERR, "— bucket {$bucket} —\n");

	foreach ($deletes as $filename) {
		try {
			$s3->deleteObject(['Bucket' => $bucket, 'Key' => $filename]);
			fwrite(STDERR, "  deleted {$filename}\n");
		} catch (Throwable $e) {
			$failed++;
			fwrite(STDERR, '  FAIL delete ' . $filename . ': ' . $e->getMessage() . "\n");
		}
	}

	foreach ($renames as $row) {
		$old = $row['old'];
		$new = $row['new'];
		try {
			$s3->copyObject([
				'Bucket' => $bucket,
				'Key' => $new,
				'CopySource' => $bucket . '/' . rawurlencode($old),
			]);
			$s3->deleteObject(['Bucket' => $bucket, 'Key' => $old]);
			fwrite(STDERR, "  renamed {$old} -> {$new}\n");
		} catch (Throwable $e) {
			$failed++;
			fwrite(STDERR, '  FAIL rename ' . $old . ': ' . $e->getMessage() . "\n");
		}
	}
}

// Refresh local caches to match new keys.
$dates = pinchard_exif_dates_cache_read();
foreach ($deletes as $filename) {
	unset($dates[$filename]);
}
foreach ($renames as $row) {
	if (isset($dates[$row['old']])) {
		$dates[$row['new']] = $dates[$row['old']];
		unset($dates[$row['old']]);
	}
}
pinchard_exif_dates_cache_write($dates);

foreach ($buckets as $bucket) {
	$path = pinchard_s3_list_cache_path($bucket);
	if (is_file($path)) {
		unlink($path);
		fwrite(STDERR, "cleared S3 list cache {$path}\n");
	}
}

$metaDir = pinchard_photo_cache_dir() . '/exif-meta';
if (is_dir($metaDir)) {
	foreach (array_merge($deletes, array_column($renames, 'old')) as $filename) {
		$metaPath = $metaDir . '/' . sha1($filename) . '.json';
		if (is_file($metaPath)) {
			unlink($metaPath);
		}
	}
}

$mappingPath = pinchard_photo_cache_dir() . '/rename-to-exif-log.json';
file_put_contents($mappingPath, json_encode([
	'ran_at' => time(),
	'min_delta' => $minDelta,
	'deletes' => $deletes,
	'renames' => $renames,
	'failed' => $failed,
], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

fwrite(STDERR, "Done. failed={$failed} log={$mappingPath}\n");
fwrite(STDERR, "Note: production DreamHost .cache/ is separate — clear s3-list-*.json there or wait for TTL, then re-run cache-exif-dates if needed.\n");
fwrite(STDERR, "CloudFront may serve old URLs briefly; invalidate both distributions if bookmarks must break cleanly.\n");

exit($failed > 0 ? 1 : 0);
