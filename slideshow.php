<?php

declare(strict_types=1);

/**
 * Legacy slideshow URL — permanently redirects into the photo viewer autoplay mode.
 * Forwards display / fade / kiosk query params.
 */

require_once __DIR__ . '/lib/bootstrap.php';

$query = ['play' => '1'];
if (isset($_GET['display']) && $_GET['display'] !== '') {
	$query['display'] = (string) $_GET['display'];
}
if (isset($_GET['fade']) && $_GET['fade'] !== '') {
	$query['fade'] = (string) $_GET['fade'];
}
if (isset($_GET['kiosk']) && $_GET['kiosk'] !== '' && $_GET['kiosk'] !== '0') {
	$query['kiosk'] = '1';
}
if (isset($_GET['filename']) && $_GET['filename'] !== '') {
	$query['filename'] = (string) $_GET['filename'];
}

header('Location: ' . pinchard_absolute_url('/index.php', $query), true, 301);
exit;
