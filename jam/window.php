<?php

declare(strict_types=1);

/**
 * Cloudberry Jam — window mode defaults; thumbnails bucket + CDN.
 */
ini_set('display_errors', '0');

$display = isset($_GET['display']) && $_GET['display'] !== '' ? $_GET['display'] : 10;
$fade = isset($_GET['fade']) && $_GET['fade'] !== '' ? $_GET['fade'] : 2;

require_once __DIR__ . '/../functions_inc.php';

[$cdnurl, $array] = pinchard_jam_photo_list('thumbnails');

$jam_page_title = 'Cloudberry Jam';
$jam_layout = 'crop';
$jam_start = 0;
require __DIR__ . '/../lib/partials/jam-page.php';
