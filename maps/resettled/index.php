<?php

declare(strict_types=1);

require_once __DIR__ . '/../../lib/partials/microsite.php';
require_once __DIR__ . '/../../lib/partials/maps.php';

$pageTitle = 'Resettled Communities';
$description = 'Map of resettled outport communities around Pinchard\'s Island, Newfoundland — historical resettlement in Notre Dame Bay.';
$canonical = pinchard_absolute_url('/maps/resettled/');

pinchard_microsite_head($pageTitle, [
	'body_attr' => 'id="page-top" class="maps-embed-page"',
	'description' => $description,
	'canonical_url' => $canonical,
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

pinchard_maps_nav('resettled');
?>
    <h1 class="visually-hidden"><?= pinchard_h($pageTitle) ?> — map of Notre Dame Bay, Newfoundland</h1>
    <p class="visually-hidden"><?= pinchard_h($description) ?></p>
    <div class="maps-embed-shell">
        <iframe
            class="maps-embed-frame"
            src="https://www.google.com/maps/d/u/0/embed?mid=1-gIU1rTeKAwvGmqoiJZefa8p-qc"
            allowfullscreen
            title="Resettled Communities map"
        ></iframe>
    </div>

<?php pinchard_microsite_scripts_footer(); ?>
