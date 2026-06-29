<?php

declare(strict_types=1);

require_once __DIR__ . '/../../lib/partials/microsite.php';
require_once __DIR__ . '/../../lib/partials/maps.php';

$micrositeBase = pinchard_maps_subsite_base();

pinchard_microsite_head('53 Trees', [
	'body_attr' => 'id="page-top" class="maps-embed-page"',
	'base_path' => $micrositeBase,
	'google_fonts' => true,
	'font_awesome' => true,
]);

pinchard_maps_nav('trees', ['base_path' => $micrositeBase, 'at_maps_root' => false]);
?>
    <div class="maps-embed-shell">
        <div class="maps-embed-viewport">
            <iframe
                src="https://www.google.com/maps/d/u/0/embed?mid=19NfRJjMQjtei3GXok6oK9WOqnsw"
                allowfullscreen
                title="53 Trees map"
            ></iframe>
        </div>
    </div>

<?php pinchard_microsite_scripts_footer(['base_path' => $micrositeBase]); ?>
