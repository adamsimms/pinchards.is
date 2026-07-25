<?php

declare(strict_types=1);

/**
 * Stage local shutter-island-deleted GoPro JPEGs for Cloudberry R2 ingest.
 *
 * - Keeps GOPR*.JPG only (skips PiCamera / MP4)
 * - Renames object keys to EXIF DateTimeOriginal: {Y-m-d\TH:i:s}.000Z_{GOPR####.JPG}
 * - Dedupes bulk-download copies that collide on the same EXIF key
 * - Copies masters to staging/full and generates 300×225 thumbs
 *
 * Usage:
 *   php scripts/ingest-local-cloudberry.php \
 *     [--source=~/Downloads/shutter-island-deleted] \
 *     [--out=~/Downloads/cloudberry-ingest]
 */

require_once dirname(__DIR__) . '/lib/bootstrap.php';

$sourceDir = getenv('HOME') . '/Downloads/shutter-island-deleted';
$outDir = getenv('HOME') . '/Downloads/cloudberry-ingest';

foreach (array_slice($argv, 1) as $arg) {
	if (str_starts_with($arg, '--source=')) {
		$sourceDir = expand_home(substr($arg, 9));
	} elseif (str_starts_with($arg, '--out=')) {
		$outDir = expand_home(substr($arg, 6));
	} elseif ($arg === '--help' || $arg === '-h') {
		fwrite(STDERR, "Usage: php scripts/ingest-local-cloudberry.php [--source=DIR] [--out=DIR]\n");
		exit(0);
	}
}

if (!is_dir($sourceDir)) {
	fwrite(STDERR, "Source not found: {$sourceDir}\n");
	exit(1);
}

$fullDir = $outDir . '/full';
$thumbsDir = $outDir . '/thumbs';
foreach ([$outDir, $fullDir, $thumbsDir] as $dir) {
	if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
		fwrite(STDERR, "Cannot create {$dir}\n");
		exit(1);
	}
}

$entries = scandir($sourceDir) ?: [];
$candidates = [];
$skipped = ['picamera' => 0, 'mp4' => 0, 'other' => 0];

foreach ($entries as $name) {
	if ($name === '.' || $name === '..') {
		continue;
	}
	$path = $sourceDir . '/' . $name;
	if (!is_file($path)) {
		continue;
	}
	if (stripos($name, 'PiCamera') !== false) {
		$skipped['picamera']++;
		continue;
	}
	if (preg_match('/\.mp4$/i', $name)) {
		$skipped['mp4']++;
		continue;
	}
	if (!preg_match('/^(?:.+)_(GOPR\d+\.JPG)$/i', $name, $m)) {
		$skipped['other']++;
		continue;
	}

	$exif = @exif_read_data($path, 'ANY_TAG', true) ?: [];
	$dto = $exif['EXIF']['DateTimeOriginal'] ?? $exif['IFD0']['DateTime'] ?? null;
	if (!is_string($dto) || $dto === '') {
		fwrite(STDERR, "Missing DateTimeOriginal: {$name}\n");
		exit(1);
	}
	$dt = DateTime::createFromFormat('Y:m:d H:i:s', $dto);
	if (!$dt instanceof DateTime) {
		fwrite(STDERR, "Bad DateTimeOriginal '{$dto}' in {$name}\n");
		exit(1);
	}

	$gopr = $m[1];
	// Preserve existing archive casing (.JPG)
	if (preg_match('/^(GOPR\d+)(\.\w+)$/i', $gopr, $gm)) {
		$gopr = strtoupper($gm[1]) . '.JPG';
	}
	$key = $dt->format('Y-m-d\TH:i:s') . '.000Z_' . $gopr;

	$candidates[] = [
		'source' => $name,
		'path' => $path,
		'key' => $key,
		'exif' => $dto,
		'date' => $dt->format('Y/m/d H:i:s'),
		'show_date' => pinchard_show_date($dt),
		'name_matches_key' => ($name === $key),
	];
}

/** @var array<string, list<array<string, mixed>>> $byKey */
$byKey = [];
foreach ($candidates as $c) {
	$byKey[$c['key']][] = $c;
}

$manifest = [
	'generatedAt' => gmdate('c'),
	'sourceDir' => $sourceDir,
	'outDir' => $outDir,
	'skipped' => $skipped,
	'sourceCount' => count($candidates),
	'uniqueKeys' => count($byKey),
	'items' => [],
	'droppedDupes' => [],
];

$n = 0;
$total = count($byKey);
foreach ($byKey as $key => $group) {
	$n++;
	usort($group, static function (array $a, array $b): int {
		// Prefer source whose filename already matches the EXIF key
		if ($a['name_matches_key'] !== $b['name_matches_key']) {
			return $a['name_matches_key'] ? -1 : 1;
		}

		return strcmp($a['source'], $b['source']);
	});

	$winner = $group[0];
	foreach (array_slice($group, 1) as $loser) {
		$manifest['droppedDupes'][] = [
			'source' => $loser['source'],
			'key' => $key,
			'exif' => $loser['exif'],
			'action' => 'drop-dupe',
			'keptSource' => $winner['source'],
		];
	}

	$action = $winner['name_matches_key'] ? 'keep' : 'rename';
	$destFull = $fullDir . '/' . $key;
	$destThumb = $thumbsDir . '/' . $key;

	if (!copy($winner['path'], $destFull)) {
		fwrite(STDERR, "Copy failed: {$winner['source']} -> {$destFull}\n");
		exit(1);
	}

	make_thumb_300x225($destFull, $destThumb);

	$manifest['items'][] = [
		'source' => $winner['source'],
		'key' => $key,
		'exif' => $winner['exif'],
		'date' => $winner['date'],
		'show_date' => $winner['show_date'],
		'action' => $action,
	];

	if ($n % 25 === 0 || $n === $total) {
		fwrite(STDERR, "[{$n}/{$total}] staged {$key}\n");
	}
}

$manifestPath = $outDir . '/manifest.json';
file_put_contents(
	$manifestPath,
	json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
);

fwrite(
	STDERR,
	'Done. unique=' . count($manifest['items'])
	. ' dropped_dupes=' . count($manifest['droppedDupes'])
	. ' skipped_picamera=' . $skipped['picamera']
	. ' skipped_mp4=' . $skipped['mp4']
	. "\nManifest: {$manifestPath}\n"
);

function expand_home(string $path): string
{
	if (str_starts_with($path, '~/')) {
		return getenv('HOME') . substr($path, 1);
	}

	return $path;
}

function make_thumb_300x225(string $src, string $dest): void
{
	// sips: -z height width
	$cmd = sprintf(
		'sips -z 225 300 -s format jpeg -s formatOptions 70 %s --out %s 2>/dev/null',
		escapeshellarg($src),
		escapeshellarg($dest)
	);
	exec($cmd, $out, $code);
	if ($code !== 0 || !is_file($dest)) {
		// GD fallback
		$img = @imagecreatefromjpeg($src);
		if ($img === false) {
			fwrite(STDERR, "Thumb failed for {$src}\n");
			exit(1);
		}
		$thumb = imagecreatetruecolor(300, 225);
		imagecopyresampled($thumb, $img, 0, 0, 0, 0, 300, 225, imagesx($img), imagesy($img));
		imagejpeg($thumb, $dest, 70);
		imagedestroy($img);
		imagedestroy($thumb);
	}

	$size = getimagesize($dest);
	if ($size === false || $size[0] !== 300 || $size[1] !== 225) {
		fwrite(STDERR, "Unexpected thumb size for {$dest}: " . json_encode($size) . "\n");
		exit(1);
	}
}
