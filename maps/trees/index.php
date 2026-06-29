<?php

declare(strict_types=1);

require_once __DIR__ . '/../../lib/partials/microsite.php';
require_once __DIR__ . '/../../lib/partials/maps.php';

pinchard_microsite_head('53 Trees', [
	'body_attr' => 'id="page-top" class="maps-embed-page"',
	'google_fonts' => true,
	'font_awesome' => true,
]);

pinchard_maps_nav('trees');
?>
    <div class="maps-embed-shell">
        <iframe
            class="maps-embed-frame"
            src="https://www.google.com/maps/d/u/0/embed?mid=19NfRJjMQjtei3GXok6oK9WOqnsw"
            allowfullscreen
            title="53 Trees map"
        ></iframe>
    </div>

<?php pinchard_microsite_scripts_footer(); ?>
