<?php

declare(strict_types=1);

/**
 * Backfill images/photo/.cache/exif-dates.json from DateTimeOriginal.
 *
 * Usage: php scripts/cache-exif-dates.php [--limit=N] [--offset=N]
 */

require_once dirname(__DIR__) . '/lib/bootstrap.php';

$limit = null;
$offset = 0;
foreach (array_slice($argv, 1) as $arg) {
	if (str_starts_with($arg, '--limit=')) {
		$limit = max(0, (int) substr($arg, 8));
	} elseif (str_starts_with($arg, '--offset=')) {
		$offset = max(0, (int) substr($arg, 9));
	}
}

$cfg = pinchard_config();
$photos = getObjectList($cfg['s3_bucket_thumbnails']);
usort($photos, static fn ($a, $b) => $a['date'] <=> $b['date']);
$total = count($photos);
$slice = array_slice($photos, $offset, $limit);
$dates = pinchard_exif_dates_cache_read();
$updated = 0;
$failed = 0;

fwrite(STDERR, 'Caching EXIF dates for ' . count($slice) . " of {$total} photographs (offset {$offset})…\n");

foreach ($slice as $i => $photo) {
	$filename = $photo['filename'];
	$n = $offset + $i + 1;
	$exif = pinchard_read_photo_exif($filename, $cfg['cdn_url_full']);
	$captureDt = pinchard_photo_capture_datetime($photo['date'], $exif);
	$hasExif = isset($exif['EXIF']['DateTimeOriginal']) || isset($exif['IFD0']['DateTime']);
	if ($captureDt === null || !$hasExif) {
		$failed++;
		fwrite(STDERR, "[{$n}/{$total}] FAIL {$filename}\n");
		continue;
	}
	$formatted = $captureDt->format('Y/m/d H:i:s');
	if (($dates[$filename] ?? null) !== $formatted) {
		$dates[$filename] = $formatted;
		$updated++;
	}
	if ($n % 25 === 0 || $i === count($slice) - 1) {
		pinchard_exif_dates_cache_write($dates);
		fwrite(STDERR, "[{$n}/{$total}] cached={$updated} failed={$failed}\n");
	}
}

pinchard_exif_dates_cache_write($dates);
fwrite(STDERR, "Done. updated={$updated} failed={$failed} total_cached=" . count($dates) . "\n");
