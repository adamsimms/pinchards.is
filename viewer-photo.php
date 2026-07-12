<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';

pinchard_rate_limit('viewer-photo', 600, 3600);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=300');

$filename = isset($_GET['filename']) ? (string) $_GET['filename'] : '';
if ($filename === '') {
	http_response_code(400);
	echo json_encode(['error' => 'Missing filename parameter.'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit;
}

try {
	$cfg = pinchard_config();
	$array = getObjectList($cfg['s3_bucket_thumbnails']);
	usort($array, fn ($a, $b) => $a['date'] <=> $b['date']);

	$allowed = false;
	foreach ($array as $photo) {
		if ($photo['filename'] === $filename) {
			$allowed = true;
			break;
		}
	}

	if (!$allowed) {
		http_response_code(404);
		echo json_encode(['error' => 'Photograph not found.'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		exit;
	}

	$datetime = '';
	foreach ($array as $photo) {
		if ($photo['filename'] === $filename) {
			$datetime = $photo['date'];
			break;
		}
	}

	$galleryContext = pinchard_gallery_context_for_photo($datetime);
	$viewerTimeline = pinchard_viewer_timeline($array, $filename, $galleryContext);
	$payload = pinchard_viewer_photo_payload($array, $filename, $galleryContext, $cfg['cdn_url_full'], $viewerTimeline);

	echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
	http_response_code(503);
	echo json_encode(['error' => 'Photo metadata is temporarily unavailable.'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
