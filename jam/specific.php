<?php

declare(strict_types=1);

/**
 * Cloudberry Jam — start at ?start= index into full-res list (full bucket + CDN).
 */
ini_set('display_errors', '0');

$display = isset($_GET['display']) && $_GET['display'] !== '' ? $_GET['display'] : 0.01;
$fade = isset($_GET['fade']) && $_GET['fade'] !== '' ? $_GET['fade'] : 6;
$jam_start = isset($_GET['start']) && $_GET['start'] !== '' ? (int) $_GET['start'] : 0;
if ($jam_start < 0) {
	$jam_start = 0;
}

require_once __DIR__ . '/../functions_inc.php';

[$cdnurl, $array] = pinchard_jam_photo_list('full');

$jam_page_title = 'Cloudberry Jam';
$jam_layout = 'crop';
require __DIR__ . '/../lib/partials/jam-page.php';
