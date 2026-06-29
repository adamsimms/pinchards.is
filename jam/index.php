<?php

declare(strict_types=1);

/**
 * Cloudberry Jam — fullscreen exhibition slideshow (direct URL only).
 *
 * Query params:
 *   layout=crop|fill   default fill (full-bleed for projection)
 *   shuffle=1          random order
 *   start=N            begin at index N in chronological list
 *   display=SECONDS    hold time per image (default 0.01)
 *   fade=SECONDS       crossfade duration (default 6)
 */
ini_set('display_errors', '0');

$display = isset($_GET['display']) && $_GET['display'] !== '' ? $_GET['display'] : 0.01;
$fade = isset($_GET['fade']) && $_GET['fade'] !== '' ? $_GET['fade'] : 6;

$jam_layout = (isset($_GET['layout']) && $_GET['layout'] === 'crop') ? 'crop' : 'fill';

$jam_start = isset($_GET['start']) && $_GET['start'] !== '' ? (int) $_GET['start'] : 0;
if ($jam_start < 0) {
	$jam_start = 0;
}

try {
	require_once __DIR__ . '/../functions_inc.php';

	[$cdnurl, $array] = pinchard_jam_photo_list();
} catch (RuntimeException | \Aws\Exception\AwsException $e) {
	http_response_code(503);
	header('Content-Type: text/plain; charset=utf-8');
	if (pinchard_env_non_empty('PINCHARD_DEBUG') === '1') {
		exit($e->getMessage());
	}
	exit('Jam slideshow is temporarily unavailable.');
}

if (isset($_GET['shuffle']) && $_GET['shuffle'] !== '' && $_GET['shuffle'] !== '0') {
	shuffle($array);
}

$jam_page_title = 'Cloudberry Jam';
require __DIR__ . '/../lib/partials/jam-page.php';
