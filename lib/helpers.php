<?php

declare(strict_types=1);

/** Escape HTML text or attribute values. */
function pinchard_h(?string $value): string
{
	return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Current request URL without query string (for og:url). */
function pinchard_canonical_url(): string
{
	$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
	$host = $_SERVER['HTTP_HOST'] ?? 'www.pinchards.is';
	$uri = $_SERVER['REQUEST_URI'] ?? '/';
	$path = strtok($uri, '?') ?: '/';

	return $scheme . '://' . $host . $path;
}

/**
 * Group photos chronologically by Y-m month key.
 *
 * @param list<array{filename: string, date: string, show_date?: string}> $photos
 * @return array<string, array{label: string, photos: list<array{filename: string, date: string, show_date?: string}>}>
 */
function pinchard_group_photos_by_month(array $photos): array
{
	$photosByMonth = [];
	foreach ($photos as $photo) {
		$dt = DateTime::createFromFormat('Y/m/d H:i:s', $photo['date']);
		if ($dt === false) {
			continue;
		}
		$monthKey = $dt->format('Y-m');
		if (!isset($photosByMonth[$monthKey])) {
			$photosByMonth[$monthKey] = [
				'label' => $dt->format('F Y'),
				'photos' => [],
			];
		}
		$photosByMonth[$monthKey]['photos'][] = $photo;
	}

	return $photosByMonth;
}

/**
 * @return array{month_key: string, label: string, gallery_url: string}|null
 */
function pinchard_gallery_context_for_photo(string $date): ?array
{
	$dt = DateTime::createFromFormat('Y/m/d H:i:s', $date);
	if ($dt === false) {
		return null;
	}
	$monthKey = $dt->format('Y-m');

	return [
		'month_key' => $monthKey,
		'label' => $dt->format('F Y'),
		'gallery_url' => 'gallery.php#month-' . $monthKey,
	];
}

/** Compact label for gallery timeline scrubber (e.g. Aug 2017). */
function pinchard_month_timeline_label(string $monthKey): string
{
	$dt = DateTime::createFromFormat('Y-m', $monthKey);
	if ($dt === false) {
		return $monthKey;
	}

	return $dt->format('M Y');
}

/**
 * @param list<array{filename: string, date: string, show_date?: string}> $photos
 */
function pinchard_latest_photo(array $photos): ?array
{
	if ($photos === []) {
		return null;
	}

	return $photos[count($photos) - 1];
}

/** Convert an EXIF GPS rational string (or number) to float. */
function pinchard_gps_rational_to_float(mixed $coordPart): float
{
	if (is_int($coordPart) || is_float($coordPart)) {
		return (float) $coordPart;
	}
	if (!is_string($coordPart)) {
		return 0.0;
	}
	$parts = explode('/', $coordPart);
	if (count($parts) === 1) {
		return (float) $parts[0];
	}
	if (count($parts) >= 2 && (float) $parts[1] !== 0.0) {
		return (float) $parts[0] / (float) $parts[1];
	}

	return 0.0;
}

/** @param list<mixed> $exifCoord */
function pinchard_gps_to_decimal(array $exifCoord, ?string $hemi): ?float
{
	if ($hemi === null || $hemi === '') {
		return null;
	}
	$degrees = count($exifCoord) > 0 ? pinchard_gps_rational_to_float($exifCoord[0]) : 0.0;
	$minutes = count($exifCoord) > 1 ? pinchard_gps_rational_to_float($exifCoord[1]) : 0.0;
	$seconds = count($exifCoord) > 2 ? pinchard_gps_rational_to_float($exifCoord[2]) : 0.0;
	$flip = ($hemi === 'W' || $hemi === 'S') ? -1 : 1;

	return $flip * ($degrees + $minutes / 60 + $seconds / 3600);
}

/** Display title for a gallery photo (GoPro: digits after GOPR). */
function pinchard_photo_title(string $filename): string
{
	$basename = pathinfo($filename, PATHINFO_FILENAME);
	if (preg_match('/GOPR(\d+)/i', $basename, $matches)) {
		return $matches[1];
	}

	return $basename;
}

/**
 * Resolve a gallery photo by filename (allowlist). Unknown fn falls back to latest photo.
 *
 * @param list<array{filename: string, date: string, show_date?: string}> $photos
 * @return array{photo: array, prev_filename: ?string, next_filename: ?string}
 */
function pinchard_resolve_gallery_photo(array $photos, ?string $requested): array
{
	if ($photos === []) {
		throw new RuntimeException('Photo gallery is empty.');
	}

	if ($requested !== null && $requested !== '') {
		foreach ($photos as $i => $photo) {
			if ($photo['filename'] === $requested) {
				return [
					'photo' => $photo,
					'prev_filename' => $i > 0 ? $photos[$i - 1]['filename'] : null,
					'next_filename' => $i < count($photos) - 1 ? $photos[$i + 1]['filename'] : null,
				];
			}
		}
	}

	$i = count($photos) - 1;

	return [
		'photo' => $photos[$i],
		'prev_filename' => $i > 0 ? $photos[$i - 1]['filename'] : null,
		'next_filename' => null,
	];
}

/** Simple per-IP rate limit for lightweight JSON proxies. */
function pinchard_rate_limit(string $bucket, int $maxRequests, int $windowSeconds = 3600): void
{
	$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
	$file = sys_get_temp_dir() . '/pinchard_rl_' . $bucket . '_' . md5($ip) . '.json';
	$now = time();
	$data = ['count' => 0, 'start' => $now];

	if (is_readable($file)) {
		$decoded = json_decode((string) file_get_contents($file), true);
		if (is_array($decoded) && isset($decoded['count'], $decoded['start'])) {
			$data = $decoded;
		}
	}

	if ($now - (int) $data['start'] >= $windowSeconds) {
		$data = ['count' => 0, 'start' => $now];
	}

	$data['count'] = (int) $data['count'] + 1;
	file_put_contents($file, json_encode($data), LOCK_EX);

	if ($data['count'] > $maxRequests) {
		http_response_code(429);
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode(['error' => 'Rate limit exceeded. Try again later.']);
		exit;
	}
}
