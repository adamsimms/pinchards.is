<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/env.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/geomet.php';

pinchard_rate_limit('weather', 120, 3600);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=600');

$lat = PINCHARD_WEATHER_LAT;
$lon = PINCHARD_WEATHER_LON;

if (isset($_GET['lat'], $_GET['lon']) && is_numeric($_GET['lat']) && is_numeric($_GET['lon'])) {
	$lat = (float) $_GET['lat'];
	$lon = (float) $_GET['lon'];
}

$payload = pinchard_geomet_weather_payload($lat, $lon);

if (isset($payload['error'])) {
	http_response_code(502);
}

echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
