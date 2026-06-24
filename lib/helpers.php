<?php

declare(strict_types=1);

/** Escape HTML text or attribute values. */
function pinchard_h(?string $value): string
{
	return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
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
