<?php

declare(strict_types=1);

require_once __DIR__ . '/microsite.php';

const PINCHARD_MAPBOX_GL_VERSION = '3.25.0';
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
	if ($options['kiosk'] ?? false) {
		return;
	}

	$mapsHref = pinchard_h('/maps/');
	$galleryHref = pinchard_h(pinchard_microsite_asset_url('gallery.php'));
	?>
    <nav id="mainNav" class="navbar navbar-default fixed-top" aria-label="Maps">
        <div class="nav-bar-inner">
            <div class="nav-bar-start">
                <a href="<?= $mapsHref ?>" class="title-brand">Maps</a>
            </div>
            <div class="nav-bar-end maps-subnav" aria-label="Map views">
<?php foreach (pinchard_maps_sections() as $section): ?>
<?php
	$isActive = $active === $section['slug'];
	$href = pinchard_h($section['href']);
	$label = pinchard_h($section['title']);
?>
                <a href="<?= $href ?>" class="maps-subnav-link<?= $isActive ? ' is-active' : '' ?>"<?= $isActive ? ' aria-current="page"' : '' ?>><?= $label ?></a>
<?php endforeach; ?>
                <a href="<?= $galleryHref ?>" class="link-to-gallery nav_cloudberry" aria-label="Browse photo gallery"></a>
            </div>
        </div>
    </nav>
<?php
}

function pinchard_mapbox_gl_css(): string
{
	$version = PINCHARD_MAPBOX_GL_VERSION;

	return '    <link href="https://api.mapbox.com/mapbox-gl-js/v' . $version . '/mapbox-gl.css" rel="stylesheet">';
}

function pinchard_mapbox_gl_js(): string
{
	$version = PINCHARD_MAPBOX_GL_VERSION;

	return '    <script src="https://api.mapbox.com/mapbox-gl-js/v' . $version . '/mapbox-gl.js"></script>';
}

function pinchard_mapbox_gl_assets(): string
{
	return pinchard_mapbox_gl_css() . "\n" . pinchard_mapbox_gl_js();
}
