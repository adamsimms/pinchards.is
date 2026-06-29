<?php

declare(strict_types=1);

require_once __DIR__ . '/../../lib/partials/microsite.php';
require_once __DIR__ . '/../../lib/partials/maps.php';

$micrositeBase = pinchard_maps_subsite_base();

pinchard_microsite_head('Resettled Communities', [
	'body_attr' => 'id="page-top" class="maps-embed-page"',
	'base_path' => $micrositeBase,
	'google_fonts' => true,
	'font_awesome' => true,
]);

pinchard_maps_nav('resettled', ['base_path' => $micrositeBase, 'at_maps_root' => false]);
?>
    <div class="maps-embed-shell">
        <div class="maps-embed-viewport">
            <iframe
                src="https://www.google.com/maps/d/u/0/embed?mid=1-gIU1rTeKAwvGmqoiJZefa8p-qc"
                allowfullscreen
                title="Resettled Communities map"
            ></iframe>
        </div>
    </div>

<?php pinchard_microsite_scripts_footer(['base_path' => $micrositeBase]); ?>
