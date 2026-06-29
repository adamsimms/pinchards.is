<?php

declare(strict_types=1);

require_once __DIR__ . '/../../lib/partials/microsite.php';
require_once __DIR__ . '/../../lib/partials/maps.php';

pinchard_microsite_head('Resettled Communities', [
	'body_attr' => 'id="page-top" class="maps-embed-page"',
	'font_awesome' => true,
]);

pinchard_maps_nav('resettled');
?>
    <div class="maps-embed-shell">
        <iframe
            class="maps-embed-frame"
            src="https://www.google.com/maps/d/u/0/embed?mid=1-gIU1rTeKAwvGmqoiJZefa8p-qc"
            allowfullscreen
            title="Resettled Communities map"
        ></iframe>
    </div>

<?php pinchard_microsite_scripts_footer(); ?>
