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
