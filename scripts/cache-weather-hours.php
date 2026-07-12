<?php

declare(strict_types=1);

/**
 * Backfill images/photo/.cache/weather-hours.json from Open-Meteo ERA5
 * for every distinct capture day in the archive (EXIF-preferred).
 *
 * Usage: php scripts/cache-weather-hours.php [--chunk-days=31] [--force]
 */
require_once dirname(__DIR__) . '/lib/bootstrap.php';

$chunkDays = 31;
$force = false;
foreach (array_slice($argv, 1) as $arg) {
	if (str_starts_with($arg, '--chunk-days=')) {
		$chunkDays = max(1, (int) substr($arg, 13));
	} elseif ($arg === '--force') {
		$force = true;
	}
}

$cfg = pinchard_config();
$photos = getObjectList($cfg['s3_bucket_thumbnails']);
$exifDates = pinchard_exif_dates_cache_read();

$days = [];
foreach ($photos as $photo) {
	$filename = $photo['filename'];
	$wall = $exifDates[$filename] ?? ($photo['capture_date'] ?? $photo['date'] ?? null);
	if (!is_string($wall) || $wall === '') {
		continue;
	}
	$dt = DateTime::createFromFormat('Y/m/d H:i:s', $wall);
	if (!$dt instanceof DateTime) {
		$dt = DateTime::createFromFormat('Y-m-d H:i:s', $wall);
	}
	if (!$dt instanceof DateTime) {
		continue;
	}
	$days[$dt->format('Y-m-d')] = true;
}

$dayList = array_keys($days);
sort($dayList);
$totalDays = count($dayList);

if ($totalDays === 0) {
	fwrite(STDERR, "No capture days found. Run scripts/cache-exif-dates.php first.\n");
	exit(1);
}

$cache = pinchard_weather_cache_read();
$missingDays = [];
foreach ($dayList as $day) {
	if ($force) {
		$missingDays[] = $day;
		continue;
	}
	// A day is warm if noon hour exists (proxy for a full daily fetch).
	$probe = $day . 'T12';
	if (!isset($cache[$probe])) {
		$missingDays[] = $day;
	}
}

fwrite(STDERR, 'Weather prewarm: ' . count($missingDays) . " of {$totalDays} capture days to fetch"
	. ($force ? ' (--force)' : '') . ", chunk={$chunkDays}…\n");

if ($missingDays === []) {
	fwrite(STDERR, 'Done. cache already warm. hours=' . count($cache) . "\n");
	exit(0);
}

$fetchedHours = 0;
$failedChunks = 0;
$i = 0;
while ($i < count($missingDays)) {
	$chunk = array_slice($missingDays, $i, $chunkDays);
	$start = $chunk[0];
	$end = $chunk[count($chunk) - 1];
	$n = min($i + count($chunk), count($missingDays));
	fwrite(STDERR, "[{$n}/" . count($missingDays) . "] fetching {$start} → {$end}…\n");

	$hours = pinchard_weather_fetch_range($start, $end);
	if ($hours === []) {
		$failedChunks++;
		fwrite(STDERR, "  FAIL empty response for {$start} → {$end}\n");
		$i += count($chunk);
		usleep(250000);
		continue;
	}

	$fetchedHours += pinchard_weather_cache_merge($hours);
	$i += count($chunk);
	usleep(150000);
}

$cache = pinchard_weather_cache_read();
fwrite(STDERR, 'Done. hours_written≈' . $fetchedHours
	. ' failed_chunks=' . $failedChunks
	. ' total_cached_hours=' . count($cache) . "\n");
