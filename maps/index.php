<?php

declare(strict_types=1);

/**
 * Satellite view of Pinchard's Island — default /maps/ page.
 *
 * Query params:
 *   zoom=N       default 13.5
 *   kiosk=1      hide nav for projection / wall display
 *   bearing=N    map bearing in degrees (default 0)
 *   pitch=N      map pitch in degrees (default 0)
 */

require_once __DIR__ . '/../lib/env.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/partials/microsite.php';
require_once __DIR__ . '/../lib/partials/maps.php';

$token = pinchard_env_non_empty('MAPBOX_ACCESS_TOKEN');
if ($token === null) {
	http_response_code(503);
	exit('Map unavailable. Add MAPBOX_ACCESS_TOKEN to secrets.local.php on the server.');
}

if (!str_starts_with($token, 'pk.')) {
	http_response_code(503);
	exit('Map unavailable. MAPBOX_ACCESS_TOKEN must be a Mapbox public token (pk.*), not a secret token (sk.*).');
}

$view = pinchard_pinchards_island_satellite_view();
$zoom = isset($_GET['zoom']) && $_GET['zoom'] !== '' ? (float) $_GET['zoom'] : (float) $view['zoom'];
$zoom = max(8.0, min(20.0, $zoom));
$bearing = isset($_GET['bearing']) && $_GET['bearing'] !== '' ? (float) $_GET['bearing'] : 0.0;
$pitch = isset($_GET['pitch']) && $_GET['pitch'] !== '' ? (float) $_GET['pitch'] : 0.0;
$pitch = max(0.0, min(60.0, $pitch));
$kiosk = isset($_GET['kiosk']) && $_GET['kiosk'] !== '' && $_GET['kiosk'] !== '0';

$je = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
$pageTitle = 'Maps';
$description = 'Maps of Pinchard\'s Island, Newfoundland — satellite imagery, trees, and resettled communities.';
$canonical = pinchard_absolute_url('/maps/');
$bodyClass = 'maps-satellite-page' . ($kiosk ? ' maps-satellite-page--kiosk' : '');

pinchard_microsite_head($pageTitle, [
	'body_attr' => 'id="page-top" class="' . $bodyClass . '"',
	'description' => $description,
	'canonical_url' => $canonical,
	'extra_head' => pinchard_mapbox_gl_css() . "\n",
	'json_ld' => [
		[
			'@type' => 'WebPage',
			'name' => $pageTitle,
			'description' => $description,
			'url' => $canonical,
			'isPartOf' => [
				'@type' => 'WebSite',
				'name' => "Pinchard's Island",
				'url' => pinchard_absolute_url('/'),
			],
		],
	],
]);

pinchard_maps_nav('satellite', ['kiosk' => $kiosk]);
?>
    <h1 class="visually-hidden">Satellite map of Pinchard's Island, Newfoundland</h1>
    <div class="maps-satellite-shell">
        <div id="map" role="img" aria-label="Satellite map of Pinchard's Island, Newfoundland"></div>
    </div>

<?= pinchard_mapbox_gl_js() ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var primaryStyle = <?= json_encode(PINCHARD_MAPBOX_SATELLITE_STYLE, $je) ?>;
            var fallbackStyle = <?= json_encode(PINCHARD_MAPBOX_SATELLITE_FALLBACK_STYLE, $je) ?>;
            var usedFallback = false;

            var map = new mapboxgl.Map({
                accessToken: <?= json_encode($token, $je) ?>,
                container: 'map',
                style: primaryStyle,
                center: [<?= json_encode($view['lon'], $je) ?>, <?= json_encode($view['lat'], $je) ?>],
                zoom: <?= json_encode($zoom, $je) ?>,
                bearing: <?= json_encode($bearing, $je) ?>,
                pitch: <?= json_encode($pitch, $je) ?>,
                config: {
                    showPlaceLabels: false,
                    showPointOfInterestLabels: false,
                    showRoadLabels: false,
                    showTransitLabels: false,
                    showPedestrianRoads: false,
                    showRoadsAndTransit: false,
                    showAdminBoundaries: false
                }
            });

            map.addControl(new mapboxgl.NavigationControl({ visualizePitch: true }), 'top-right');

            map.on('load', function () {
                map.resize();
            });

            map.on('error', function (event) {
                if (!usedFallback) {
                    usedFallback = true;
                    map.setStyle(fallbackStyle);
                }
            });

            window.addEventListener('resize', function () {
                map.resize();
            });
        });
    </script>

<?php pinchard_microsite_scripts_footer(); ?>
