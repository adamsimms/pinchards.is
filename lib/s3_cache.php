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

function pinchard_exif_tmp_path(): string
{
	return pinchard_root() . '/images/photo/tmp.jpg';
}

function pinchard_exif_tmp_key_path(): string
{
	return pinchard_photo_cache_dir() . '/exif-key.txt';
}

/** True when tmp.jpg already matches the requested S3 object key. */
function pinchard_exif_tmp_matches_key(string $s3Key): bool
{
	$tmpPath = pinchard_exif_tmp_path();
	$keyPath = pinchard_exif_tmp_key_path();
	if (!is_readable($tmpPath) || !is_readable($keyPath)) {
		return false;
	}

	return trim((string) file_get_contents($keyPath)) === $s3Key;
}

function pinchard_exif_tmp_record_key(string $s3Key): void
{
	pinchard_ensure_photo_cache_dir();
	file_put_contents(pinchard_exif_tmp_key_path(), $s3Key, LOCK_EX);
}

/** Ensure the directory for tmp.jpg exists (sibling of .cache). */
function pinchard_ensure_exif_tmp_dir(): void
{
	$dir = dirname(pinchard_exif_tmp_path());
	if (!is_dir($dir)) {
		mkdir($dir, 0755, true);
	}
}

/**
 * Download a full-resolution photo for EXIF parsing (cached per S3 key).
 * Tries S3 first, then the public full-size CDN — same JPEG the viewer displays.
 */
function pinchard_fetch_photo_for_exif(string $filename, string $cdnUrlFull): bool
{
	$tmpPath = pinchard_exif_tmp_path();
	if (pinchard_exif_tmp_matches_key($filename) && is_readable($tmpPath)) {
		return true;
	}

	pinchard_ensure_exif_tmp_dir();
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
			$downloaded = is_readable($tmpPath);
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
		if ($bytes !== false && $bytes !== '') {
			$downloaded = file_put_contents($tmpPath, $bytes, LOCK_EX) !== false;
		}
	}

	if ($downloaded) {
		pinchard_exif_tmp_record_key($filename);
	}

	return $downloaded && is_readable($tmpPath);
}

/**
 * Read EXIF from a gallery photo, using a cached tmp copy when possible.
 *
 * @return array<string, mixed>
 */
function pinchard_read_photo_exif(string $filename, string $cdnUrlFull): array
{
	if (!function_exists('exif_read_data')) {
		return [];
	}

	try {
		if (!pinchard_fetch_photo_for_exif($filename, $cdnUrlFull)) {
			return [];
		}

		$read = exif_read_data(pinchard_exif_tmp_path(), 0, true);
		return is_array($read) ? $read : [];
	} catch (Throwable) {
		return [];
	}
}
