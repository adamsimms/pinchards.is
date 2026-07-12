<?php

declare(strict_types=1);

/** Directory for S3 list cache files and EXIF temp metadata (not served). */
function pinchard_photo_cache_dir(): string
{
	return pinchard_root() . '/images/photo/.cache';
}

/** TTL for cached S3 object listings (seconds). Set PINCHARD_S3_LIST_CACHE_TTL=0 to disable. */
function pinchard_s3_list_cache_ttl(): int
{
	$raw = pinchard_env_non_empty('PINCHARD_S3_LIST_CACHE_TTL');
	if ($raw === null) {
		return 30 * 24 * 60 * 60; // 30 days — archive site; listings change rarely
	}
	$ttl = (int) $raw;
	return max(0, $ttl);
}

function pinchard_ensure_photo_cache_dir(): void
{
	$dir = pinchard_photo_cache_dir();
	if (!is_dir($dir)) {
		mkdir($dir, 0755, true);
	}
}

function pinchard_s3_list_cache_path(string $bucket): string
{
	return pinchard_photo_cache_dir() . '/s3-list-' . md5($bucket) . '.json';
}

/** Persistent map of filename → EXIF capture time (`Y/m/d H:i:s`). */
function pinchard_exif_dates_cache_path(): string
{
	return pinchard_photo_cache_dir() . '/exif-dates.json';
}

/**
 * @return array<string, string>
 */
function pinchard_exif_dates_cache_read(): array
{
	$path = pinchard_exif_dates_cache_path();
	if (!is_readable($path)) {
		return [];
	}

	$decoded = json_decode((string) file_get_contents($path), true);
	if (!is_array($decoded) || !isset($decoded['dates']) || !is_array($decoded['dates'])) {
		return [];
	}

	$out = [];
	foreach ($decoded['dates'] as $filename => $date) {
		if (is_string($filename) && is_string($date) && $date !== '') {
			$out[$filename] = $date;
		}
	}

	return $out;
}

/**
 * @param array<string, string> $dates
 */
function pinchard_exif_dates_cache_write(array $dates): void
{
	pinchard_ensure_photo_cache_dir();
	ksort($dates);
	$payload = json_encode([
		'cached_at' => time(),
		'dates' => $dates,
	], JSON_THROW_ON_ERROR);
	file_put_contents(pinchard_exif_dates_cache_path(), $payload, LOCK_EX);
}

function pinchard_exif_dates_cache_put(string $filename, DateTime $captureDt): void
{
	$dates = pinchard_exif_dates_cache_read();
	$formatted = $captureDt->format('Y/m/d H:i:s');
	if (($dates[$filename] ?? null) === $formatted) {
		return;
	}
	$dates[$filename] = $formatted;
	pinchard_exif_dates_cache_write($dates);
}

/**
 * Overlay cached EXIF capture times onto show_date (and capture_date) labels.
 *
 * Keeps filename-derived `date` for archive sort/navigation; labels use shutter time.
 *
 * @param list<array{filename: string, date: string, show_date?: string}> $photos
 * @return list<array{filename: string, date: string, show_date: string, capture_date?: string}>
 */
function pinchard_apply_exif_dates_to_photos(array $photos): array
{
	$exifDates = pinchard_exif_dates_cache_read();
	if ($exifDates === []) {
		return $photos;
	}

	foreach ($photos as &$photo) {
		$filename = (string) ($photo['filename'] ?? '');
		$cached = $exifDates[$filename] ?? null;
		if ($cached === null) {
			continue;
		}
		$dt = DateTime::createFromFormat('Y/m/d H:i:s', $cached);
		if ($dt === false) {
			continue;
		}
		$photo['capture_date'] = $cached;
		$photo['show_date'] = pinchard_show_date($dt);
	}
	unset($photo);

	return $photos;
}

/**
 * @return list<array{filename: string, date: string, show_date: string}>|null
 */
function pinchard_s3_list_cache_read(string $bucket): ?array
{
	$ttl = pinchard_s3_list_cache_ttl();
	if ($ttl === 0) {
		return null;
	}

	$path = pinchard_s3_list_cache_path($bucket);
	if (!is_readable($path)) {
		return null;
	}

	$decoded = json_decode((string) file_get_contents($path), true);
	if (!is_array($decoded) || !isset($decoded['cached_at'], $decoded['items']) || !is_array($decoded['items'])) {
		return null;
	}

	if (time() - (int) $decoded['cached_at'] >= $ttl) {
		return null;
	}

	return $decoded['items'];
}

/**
 * @param list<array{filename: string, date: string, show_date: string}> $items
 */
function pinchard_s3_list_cache_write(string $bucket, array $items): void
{
	if (pinchard_s3_list_cache_ttl() === 0) {
		return;
	}

	pinchard_ensure_photo_cache_dir();
	$payload = json_encode([
		'cached_at' => time(),
		'bucket' => $bucket,
		'items' => $items,
	], JSON_THROW_ON_ERROR);

	file_put_contents(pinchard_s3_list_cache_path($bucket), $payload, LOCK_EX);
}

/**
 * Directory for short-lived full JPEGs used only while extracting EXIF.
 * Prefer the JSON metadata cache; these files should not accumulate.
 */
function pinchard_exif_tmp_dir(): string
{
	return pinchard_photo_cache_dir() . '/exif-tmp';
}

function pinchard_exif_tmp_path(?string $filename = null): string
{
	if ($filename === null || $filename === '') {
		return pinchard_root() . '/images/photo/tmp.jpg';
	}

	return pinchard_exif_tmp_dir() . '/' . sha1($filename) . '.jpg';
}

/** Persistent extracted EXIF (JSON) — replaces keeping full JPEGs on disk. */
function pinchard_exif_meta_dir(): string
{
	return pinchard_photo_cache_dir() . '/exif-meta';
}

function pinchard_exif_meta_path(string $filename): string
{
	return pinchard_exif_meta_dir() . '/' . sha1($filename) . '.json';
}

/** Max age for leftover EXIF temp JPEGs (seconds). Default 1 hour. */
function pinchard_exif_tmp_ttl(): int
{
	$raw = pinchard_env_non_empty('PINCHARD_EXIF_TMP_TTL');
	if ($raw === null) {
		return 60 * 60;
	}
	return max(0, (int) $raw);
}

/** Ensure the directory for EXIF temp JPEGs exists. */
function pinchard_ensure_exif_tmp_dir(): void
{
	$dir = pinchard_exif_tmp_dir();
	if (!is_dir($dir)) {
		mkdir($dir, 0755, true);
	}
	$legacyDir = dirname(pinchard_root() . '/images/photo/tmp.jpg');
	if (!is_dir($legacyDir)) {
		mkdir($legacyDir, 0755, true);
	}
}

function pinchard_ensure_exif_meta_dir(): void
{
	$dir = pinchard_exif_meta_dir();
	if (!is_dir($dir)) {
		mkdir($dir, 0755, true);
	}
}

/**
 * @return array<string, mixed>|null
 */
function pinchard_exif_meta_cache_read(string $filename): ?array
{
	$path = pinchard_exif_meta_path($filename);
	if (!is_readable($path)) {
		return null;
	}

	$decoded = json_decode((string) file_get_contents($path), true);
	if (!is_array($decoded) || !isset($decoded['exif']) || !is_array($decoded['exif'])) {
		return null;
	}

	return $decoded['exif'];
}

/**
 * @param array<string, mixed> $exif
 */
function pinchard_exif_meta_cache_write(string $filename, array $exif): void
{
	pinchard_ensure_exif_meta_dir();
	$payload = json_encode([
		'cached_at' => time(),
		'filename' => $filename,
		'exif' => $exif,
	], JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
	file_put_contents(pinchard_exif_meta_path($filename), $payload, LOCK_EX);
}

/**
 * Delete a single EXIF temp JPEG if present.
 */
function pinchard_exif_tmp_unlink(string $filename): void
{
	$path = pinchard_exif_tmp_path($filename);
	if (is_file($path)) {
		@unlink($path);
	}
	$legacy = pinchard_root() . '/images/photo/tmp.jpg';
	if (is_file($legacy)) {
		@unlink($legacy);
	}
}

/**
 * Remove leftover EXIF temp JPEGs older than TTL (or all when $forceAll).
 *
 * @return array{removed: int, bytes: int}
 */
function pinchard_exif_tmp_prune(bool $forceAll = false): array
{
	$dir = pinchard_exif_tmp_dir();
	$removed = 0;
	$bytes = 0;
	$ttl = pinchard_exif_tmp_ttl();
	$cutoff = time() - $ttl;

	if (is_dir($dir)) {
		$files = glob($dir . '/*.jpg') ?: [];
		foreach ($files as $path) {
			if (!is_file($path)) {
				continue;
			}
			$mtime = (int) filemtime($path);
			if (!$forceAll && $ttl > 0 && $mtime >= $cutoff) {
				continue;
			}
			$size = (int) filesize($path);
			if (@unlink($path)) {
				$removed++;
				$bytes += $size;
			}
		}
	}

	$legacy = pinchard_root() . '/images/photo/tmp.jpg';
	if (is_file($legacy) && ($forceAll || $ttl === 0 || (int) filemtime($legacy) < $cutoff)) {
		$size = (int) filesize($legacy);
		if (@unlink($legacy)) {
			$removed++;
			$bytes += $size;
		}
	}

	return ['removed' => $removed, 'bytes' => $bytes];
}

/**
 * Download a full-resolution photo for one-shot EXIF parsing.
 * Tries S3 first, then the public full-size CDN — same JPEG the viewer displays.
 * Caller should delete the temp file after reading (see pinchard_read_photo_exif).
 */
function pinchard_fetch_photo_for_exif(string $filename, string $cdnUrlFull): bool
{
	pinchard_ensure_exif_tmp_dir();
	$tmpPath = pinchard_exif_tmp_path($filename);
	if (is_readable($tmpPath) && filesize($tmpPath) > 0) {
		return true;
	}

	$downloaded = false;

	global $s3;
	if (isset($s3)) {
		$cfg = pinchard_config();
		try {
			$s3->getObject([
				'Bucket' => $cfg['s3_bucket_full'],
				'Key' => $filename,
				'SaveAs' => $tmpPath,
			]);
			$downloaded = is_readable($tmpPath) && filesize($tmpPath) > 0;
		} catch (Throwable) {
			$downloaded = false;
		}
	}

	if (!$downloaded) {
		$url = $cdnUrlFull . $filename;
		$context = stream_context_create([
			'http' => [
				'timeout' => 30,
				'follow_location' => 1,
			],
			'ssl' => [
				'verify_peer' => true,
				'verify_peer_name' => true,
			],
		]);
		$bytes = @file_get_contents($url, false, $context);
		if ($bytes !== false && $bytes !== '' && strncmp($bytes, "\xFF\xD8\xFF", 3) === 0) {
			$downloaded = file_put_contents($tmpPath, $bytes, LOCK_EX) !== false;
		}
	}

	return $downloaded && is_readable($tmpPath);
}

/**
 * Read EXIF from a gallery photo.
 *
 * Prefers a small JSON metadata cache. On miss, downloads the JPEG once,
 * extracts EXIF, writes the JSON cache, then deletes the JPEG so disk does
 * not accumulate multi-GB full-resolution copies.
 *
 * @return array<string, mixed>
 */
function pinchard_read_photo_exif(string $filename, string $cdnUrlFull): array
{
	if (!function_exists('exif_read_data')) {
		return [];
	}

	$cached = pinchard_exif_meta_cache_read($filename);
	if ($cached !== null) {
		// Opportunistic cleanup of any leftover temp JPEGs (~1% of requests).
		if (random_int(1, 100) === 1) {
			pinchard_exif_tmp_prune(false);
		}
		return $cached;
	}

	try {
		if (!pinchard_fetch_photo_for_exif($filename, $cdnUrlFull)) {
			return [];
		}

		$tmpPath = pinchard_exif_tmp_path($filename);
		$read = @exif_read_data($tmpPath, null, true);
		$exif = is_array($read) ? $read : [];

		if ($exif !== []) {
			try {
				pinchard_exif_meta_cache_write($filename, $exif);
			} catch (Throwable $e) {
				if (pinchard_env_non_empty('PINCHARD_DEBUG') === '1') {
					error_log('pinchard_exif_meta_cache_write: ' . $e->getMessage());
				}
			}
		}

		// Always drop the full JPEG after extraction — dates live in exif-dates.json;
		// camera/GPS live in exif-meta/*.json.
		pinchard_exif_tmp_unlink($filename);

		return $exif;
	} catch (Throwable $e) {
		pinchard_exif_tmp_unlink($filename);
		if (pinchard_env_non_empty('PINCHARD_DEBUG') === '1') {
			error_log('pinchard_read_photo_exif: ' . $e->getMessage());
		}
		return [];
	}
}
