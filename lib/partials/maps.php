<?php

declare(strict_types=1);

require_once __DIR__ . '/microsite.php';

const PINCHARD_MAPBOX_GL_VERSION = '3.25.0';
const PINCHARD_MAPBOX_SATELLITE_STYLE = 'mapbox://styles/mapbox/standard-satellite';

function pinchard_maps_hub_base(): string
{
	return '..';
}

function pinchard_maps_subsite_base(): string
{
	return '../..';
}

/**
 * @return list<array{slug: string, title: string}>
 */
function pinchard_maps_sections(): array
{
	return [
		['slug' => 'satellite', 'title' => 'Satellite'],
		['slug' => 'trees', 'title' => '53 Trees'],
		['slug' => 'resettled', 'title' => 'Resettled Communities'],
	];
}

function pinchard_maps_section_href(string $slug, bool $atMapsRoot): string
{
	if ($slug === 'satellite') {
		return $atMapsRoot ? './' : '../';
	}

	return $atMapsRoot ? $slug . '/' : '../' . $slug . '/';
}

/**
 * @param 'satellite'|'trees'|'resettled'|null $active
 * @param array{kiosk?: bool, base_path?: string, at_maps_root?: bool} $options
 */
function pinchard_maps_nav(?string $active, array $options = []): void
{
	if ($options['kiosk'] ?? false) {
		return;
	}

	$atMapsRoot = $options['at_maps_root'] ?? false;
	$base = $options['base_path'] ?? ($atMapsRoot ? pinchard_maps_hub_base() : pinchard_maps_subsite_base());
	$galleryHref = htmlspecialchars(rtrim($base, '/') . '/gallery.php', ENT_QUOTES, 'UTF-8');
	$infoHref = htmlspecialchars(rtrim($base, '/') . '/info.php', ENT_QUOTES, 'UTF-8');
	?>
    <nav id="mainNav" class="navbar navbar-default fixed-top" aria-label="Maps">
        <div class="nav-bar-inner maps-nav-inner">
            <div class="nav-bar-start">
                <a href="<?= $galleryHref ?>" class="link-to-gallery nav_cloudberry" aria-label="Browse photo gallery"></a>
            </div>
            <div class="nav-bar-center maps-subnav" aria-label="Map views">
<?php foreach (pinchard_maps_sections() as $section): ?>
<?php
	$isActive = $active === $section['slug'];
	$href = htmlspecialchars(pinchard_maps_section_href($section['slug'], $atMapsRoot), ENT_QUOTES, 'UTF-8');
	$label = htmlspecialchars($section['title'], ENT_QUOTES, 'UTF-8');
?>
                <a href="<?= $href ?>" class="maps-subnav-link<?= $isActive ? ' is-active' : '' ?>"<?= $isActive ? ' aria-current="page"' : '' ?>><?= $label ?></a>
<?php endforeach; ?>
            </div>
            <div class="nav-bar-end">
                <a class="nav_info" href="<?= $infoHref ?>" aria-label="About Cloudberry"></a>
            </div>
        </div>
    </nav>
<?php
}

function pinchard_mapbox_gl_assets(): string
{
	$version = PINCHARD_MAPBOX_GL_VERSION;

	return implode("\n", [
		'    <script src="https://api.mapbox.com/mapbox-gl-js/v' . $version . '/mapbox-gl.js"></script>',
		'    <link href="https://api.mapbox.com/mapbox-gl-js/v' . $version . '/mapbox-gl.css" rel="stylesheet">',
	]);
}
