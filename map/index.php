<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/env.php';

$token = pinchard_env_non_empty('MAPBOX_ACCESS_TOKEN');
if ($token === null) {
	http_response_code(503);
	exit('Map unavailable. Add MAPBOX_ACCESS_TOKEN to secrets.local.php on the server.');
}

if (!str_starts_with($token, 'pk.')) {
	http_response_code(503);
	exit('Map unavailable. MAPBOX_ACCESS_TOKEN must be a Mapbox public token (pk.*), not a secret token (sk.*).');
}

$je = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>Pinchard's Island Map</title>
<meta name="viewport" content="initial-scale=1,maximum-scale=1,user-scalable=no" />
<script src="https://api.mapbox.com/mapbox-gl-js/v3.24.0/mapbox-gl.js"></script>
<link href="https://api.mapbox.com/mapbox-gl-js/v3.24.0/mapbox-gl.css" rel="stylesheet" />
<style>
	body { margin: 0; padding: 0; }
	#map { position: absolute; top: 0; bottom: 0; width: 100%; }
</style>
</head>
<body>
<div id="map"></div>
<script>
	var map = new mapboxgl.Map({
		accessToken: <?= json_encode($token, $je) ?>,
		container: 'map',
		zoom: 9,
		center: [-53.4878, 49.1974],
		style: 'mapbox://styles/mapbox/satellite-v9'
	});
	map.on('error', function (event) {
		console.error('Mapbox error:', event && event.error ? event.error : event);
	});
</script>

<script async src="https://www.googletagmanager.com/gtag/js?id=G-G1XKSQNT5M"></script>
<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());
	gtag('config', 'G-G1XKSQNT5M');
</script>

</body>
</html>
