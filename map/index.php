<?php

declare(strict_types=1);

/**
 * Pinchard's Island — fullscreen exhibition map (direct URL / kiosk).
 *
 * Query params:
 *   zoom=N       default 15 (photo viewer sidebar uses 14)
 *   kiosk=1        hide nav for projection / wall display
 *   bearing=N      map bearing in degrees (default 0)
 *   pitch=N        map pitch in degrees (default 0)
 */

require_once __DIR__ . '/../lib/env.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/partials/microsite.php';

$token = pinchard_env_non_empty('MAPBOX_ACCESS_TOKEN');
if ($token === null) {
	http_response_code(503);
	exit('Map unavailable. Add MAPBOX_ACCESS_TOKEN to secrets.local.php on the server.');
}

if (!str_starts_with($token, 'pk.')) {
	http_response_code(503);
	exit('Map unavailable. MAPBOX_ACCESS_TOKEN must be a Mapbox public token (pk.*), not a secret token (sk.*).');
}

$cabin = pinchard_cloudberry_cabin_coords();
$zoom = isset($_GET['zoom']) && $_GET['zoom'] !== '' ? (float) $_GET['zoom'] : 15.0;
$zoom = max(10.0, min(20.0, $zoom));
$bearing = isset($_GET['bearing']) && $_GET['bearing'] !== '' ? (float) $_GET['bearing'] : 0.0;
$pitch = isset($_GET['pitch']) && $_GET['pitch'] !== '' ? (float) $_GET['pitch'] : 0.0;
$pitch = max(0.0, min(60.0, $pitch));
$kiosk = isset($_GET['kiosk']) && $_GET['kiosk'] !== '' && $_GET['kiosk'] !== '0';

$je = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
$pageTitle = "Pinchard's Island Map";
$description = "Satellite map of Pinchard's Island, Newfoundland — home of the Precious Memories cabin and the Cloudberry photography archive.";
$canonical = pinchard_absolute_url('/map/');

$extraHead = implode("\n", [
	'    <meta name="description" content="' . pinchard_h($description) . '">',
	'    <meta property="og:title" content="' . pinchard_h($pageTitle) . '">',
	'    <meta property="og:description" content="' . pinchard_h($description) . '">',
	'    <meta property="og:image" content="https://www.pinchards.is/images/info/pano.jpg">',
	'    <meta property="og:type" content="website">',
	'    <meta property="og:url" content="' . pinchard_h($canonical) . '">',
	'    <meta name="twitter:card" content="summary_large_image">',
	'    <link rel="canonical" href="' . pinchard_h($canonical) . '">',
	'    <script src="https://api.mapbox.com/mapbox-gl-js/v3.24.0/mapbox-gl.js"></script>',
	'    <link href="https://api.mapbox.com/mapbox-gl-js/v3.24.0/mapbox-gl.css" rel="stylesheet">',
]);

$bodyClass = 'map-page' . ($kiosk ? ' map-page--kiosk' : '');
pinchard_microsite_head($pageTitle, [
	'body_attr' => 'id="page-top" class="' . $bodyClass . '"',
	'extra_head' => $extraHead,
]);
?>
<?php if (!$kiosk): ?>
    <nav id="mainNav" class="navbar navbar-default fixed-top">
        <div class="nav-bar-inner">
            <div class="nav-bar-start">
                <a href="../gallery.php" class="link-to-gallery nav_cloudberry" aria-label="Browse photo gallery"></a>
            </div>
            <div class="nav-bar-center">
                <a href="#" class="title-brand">Pinchard's Island</a>
            </div>
            <div class="nav-bar-end">
                <a class="nav_info" href="../info.php" aria-label="About Cloudberry"></a>
            </div>
        </div>
    </nav>
<?php endif; ?>

    <div class="map-shell">
        <div id="map" role="img" aria-label="Satellite map centered on Precious Memories cabin, Pinchard's Island"></div>
<?php if (!$kiosk): ?>
        <p class="map-caption">Precious Memories cabin — Cloudberry camera location</p>
<?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var map = new mapboxgl.Map({
                accessToken: <?= json_encode($token, $je) ?>,
                container: 'map',
                style: 'mapbox://styles/mapbox/satellite-v9',
                center: [<?= json_encode($cabin['lon'], $je) ?>, <?= json_encode($cabin['lat'], $je) ?>],
                zoom: <?= json_encode($zoom, $je) ?>,
                bearing: <?= json_encode($bearing, $je) ?>,
                pitch: <?= json_encode($pitch, $je) ?>
            });

            map.addControl(new mapboxgl.NavigationControl({ visualizePitch: true }), 'top-right');

            var marker = new mapboxgl.Marker()
                .setLngLat([<?= json_encode($cabin['lon'], $je) ?>, <?= json_encode($cabin['lat'], $je) ?>])
                .setPopup(new mapboxgl.Popup({ offset: 24, closeButton: false, closeOnClick: false })
                    .setText('Precious Memories'))
                .addTo(map);

            map.on('load', function () {
                marker.togglePopup();
            });

            map.on('error', function (event) {
                console.error('Mapbox error:', event && event.error ? event.error : event);
            });
        });
    </script>

<?php pinchard_microsite_scripts_footer(); ?>
