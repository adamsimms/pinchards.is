<?php

declare(strict_types=1);

require_once __DIR__ . '/../../lib/partials/microsite.php';
require_once __DIR__ . '/../../lib/partials/maps.php';

$pageTitle = '53 Trees';
$description = 'Interactive map of 53 named trees on Pinchard\'s Island, Newfoundland — a field survey from the Cloudberry geography collection.';
$canonical = pinchard_absolute_url('/maps/trees/');

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

pinchard_maps_nav('trees');
?>
    <h1 class="visually-hidden"><?= pinchard_h($pageTitle) ?> — map of Pinchard's Island, Newfoundland</h1>
    <p class="visually-hidden"><?= pinchard_h($description) ?></p>
    <div class="maps-embed-shell">
        <iframe
            class="maps-embed-frame"
            src="https://www.google.com/maps/d/u/0/embed?mid=19NfRJjMQjtei3GXok6oK9WOqnsw"
            allowfullscreen
            title="53 Trees map"
        ></iframe>
    </div>

<?php pinchard_microsite_scripts_footer(); ?>
