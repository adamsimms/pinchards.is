<?php

declare(strict_types=1);

require_once __DIR__ . '/microsite.php';

const PINCHARD_MAPBOX_SATELLITE_STYLE = 'mapbox://styles/mapbox/standard-satellite';
const PINCHARD_MAPBOX_SATELLITE_FALLBACK_STYLE = 'mapbox://styles/mapbox/satellite-streets-v12';

/**
 * @return list<array{slug: string, title: string, href: string}>
 */
function pinchard_maps_sections(): array
{
	return [
		['slug' => 'satellite', 'title' => 'Satellite', 'href' => '/maps/'],
		['slug' => 'trees', 'title' => '53 Trees', 'href' => '/maps/trees/'],
		['slug' => 'resettled', 'title' => 'Resettled', 'href' => '/maps/resettled/'],
	];
}

/**
 * @param 'satellite'|'trees'|'resettled'|null $active
 * @param array{kiosk?: bool} $options
 */
function pinchard_maps_nav(?string $active, array $options = []): void
{
	require_once __DIR__ . '/nav.php';

	pinchard_site_nav([
		'maps_active' => $active,
		'kiosk' => $options['kiosk'] ?? false,
	]);
}

function pinchard_mapbox_gl_assets(): string
{
	return pinchard_mapbox_gl_css() . "\n" . pinchard_mapbox_gl_js();
}

/**
 * Google My Maps embed pages under /maps/trees/ and /maps/resettled/.
 *
 * @return array<string, array{
 *   slug: 'trees'|'resettled',
 *   title: string,
 *   description: string,
 *   path: string,
 *   iframe_src: string,
 *   iframe_title: string,
 *   h1_suffix: string,
 * }>
 */
function pinchard_maps_embed_pages(): array
{
	return [
		'trees' => [
			'slug' => 'trees',
			'title' => '53 Trees',
			'description' => 'Interactive map of 53 named trees on Pinchard\'s Island, Newfoundland — a field survey from the Cloudberry geography collection.',
			'path' => '/maps/trees/',
			'iframe_src' => 'https://www.google.com/maps/d/u/0/embed?mid=19NfRJjMQjtei3GXok6oK9WOqnsw',
			'iframe_title' => '53 Trees map',
			'h1_suffix' => 'map of Pinchard\'s Island, Newfoundland',
		],
		'resettled' => [
			'slug' => 'resettled',
			'title' => 'Resettled Communities',
			'description' => 'Map of resettled outport communities around Pinchard\'s Island, Newfoundland — historical resettlement in Notre Dame Bay.',
			'path' => '/maps/resettled/',
			'iframe_src' => 'https://www.google.com/maps/d/u/0/embed?mid=1-gIU1rTeKAwvGmqoiJZefa8p-qc',
			'iframe_title' => 'Resettled Communities map',
			'h1_suffix' => 'map of Notre Dame Bay, Newfoundland',
		],
	];
}

/**
 * Render a Google My Maps embed page.
 *
 * @param 'trees'|'resettled' $slug
 */
function pinchard_maps_embed_page(string $slug): void
{
	$pages = pinchard_maps_embed_pages();
	if (!isset($pages[$slug])) {
		http_response_code(404);
		exit('Map not found.');
	}

	$page = $pages[$slug];
	$canonical = pinchard_absolute_url($page['path']);

	pinchard_microsite_head($page['title'], [
		'body_attr' => 'id="page-top" class="maps-embed-page"',
		'description' => $page['description'],
		'canonical_url' => $canonical,
		'json_ld' => [
			[
				'@type' => 'WebPage',
				'name' => $page['title'],
				'description' => $page['description'],
				'url' => $canonical,
				'isPartOf' => [
					'@type' => 'WebSite',
					'name' => "Pinchard's Island",
					'url' => pinchard_absolute_url('/'),
				],
			],
		],
	]);

	pinchard_maps_nav($page['slug']);
	?>
    <h1 class="visually-hidden"><?= pinchard_h($page['title']) ?> — <?= pinchard_h($page['h1_suffix']) ?></h1>
    <p class="visually-hidden"><?= pinchard_h($page['description']) ?></p>
    <div class="maps-embed-shell">
        <iframe
            class="maps-embed-frame"
            src="<?= pinchard_h($page['iframe_src']) ?>"
            allowfullscreen
            title="<?= pinchard_h($page['iframe_title']) ?>"
        ></iframe>
    </div>

<?php
	pinchard_microsite_scripts_footer();
}
