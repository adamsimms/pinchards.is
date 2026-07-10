<?php

declare(strict_types=1);

require_once __DIR__ . '/microsite.php';
require_once __DIR__ . '/maps.php';

/**
 * Maps section links for the global nav dropdown.
 *
 * @param 'satellite'|'trees'|'resettled'|null $active
 */
function pinchard_nav_maps_dropdown(?string $active): void
{
	$triggerClass = 'maps-nav-dropdown-trigger nav_maps';
	if ($active !== null) {
		$triggerClass .= ' is-active';
	}
	?>
                <div class="maps-nav-dropdown">
                    <button type="button" class="<?= pinchard_h($triggerClass) ?>" aria-expanded="false" aria-haspopup="true" aria-controls="mapsNavDropdownMenu" id="mapsNavDropdownTrigger" aria-label="Maps"></button>
                    <div class="maps-nav-dropdown-menu" id="mapsNavDropdownMenu" role="menu" aria-labelledby="mapsNavDropdownTrigger">
                        <div class="maps-nav-dropdown-panel">
<?php foreach (pinchard_maps_sections() as $section): ?>
<?php
	$isActive = $active === $section['slug'];
	$href = pinchard_h($section['href']);
	$label = pinchard_h($section['title']);
?>
                            <a href="<?= $href ?>" class="maps-nav-dropdown-item<?= $isActive ? ' is-active' : '' ?>" role="menuitem"<?= $isActive ? ' aria-current="page"' : '' ?>><?= $label ?></a>
<?php endforeach; ?>
                        </div>
                    </div>
                </div>
<?php
}

/**
 * Global site navigation — Cloudberry logo left; gallery, slideshow, maps, info right.
 *
 * @param array{
 *   active?: 'index'|'gallery'|'slideshow'|'info'|null,
 *   maps_active?: 'satellite'|'trees'|'resettled'|null,
 *   show_slideshow?: bool,
 *   kiosk?: bool,
 * } $options
 */
function pinchard_site_nav(array $options = []): void
{
	if ($options['kiosk'] ?? false) {
		return;
	}

	$active = $options['active'] ?? null;
	$mapsActive = $options['maps_active'] ?? null;
	$showSlideshow = $options['show_slideshow'] ?? true;

	$indexHref = pinchard_h(pinchard_microsite_asset_url('index.php'));
	$galleryHref = pinchard_h(pinchard_microsite_asset_url('gallery.php'));
	$slideshowHref = pinchard_h(pinchard_microsite_asset_url('slideshow.php'));
	$infoHref = pinchard_h(pinchard_microsite_asset_url('info.php'));

	$galleryClass = 'link-to-gallery nav_gallery' . ($active === 'gallery' ? ' active' : '');
	$infoClass = 'nav_info' . ($active === 'info' ? ' active' : '');
	$slideshowClass = 'nav_slideshow' . ($active === 'slideshow' ? ' active' : '');
	?>
    <nav id="mainNav" class="navbar navbar-default fixed-top" aria-label="Site">
        <div class="nav-bar-inner">
            <div class="nav-bar-start">
                <a href="<?= $indexHref ?>" class="title-brand"<?= $active === 'index' ? ' aria-current="page"' : '' ?>>
                    <span class="title-brand-mark" aria-hidden="true"></span>
                    <span class="title-brand-text">Cloudberry</span>
                </a>
            </div>
            <div class="nav-bar-end">
                <a href="<?= $galleryHref ?>" class="<?= pinchard_h($galleryClass) ?>" aria-label="Browse photo gallery"></a>
<?php if ($showSlideshow): ?>
<?php if ($active === 'slideshow'): ?>
                <button type="button" class="nav-slideshow-control" id="navSlideshowToggle" aria-label="Pause slideshow">
                    <span class="nav-slideshow-icons" aria-hidden="true">
                        <span class="nav-slideshow-icon nav-slideshow-icon--pause"></span>
                        <span class="nav-slideshow-icon nav-slideshow-icon--play"></span>
                    </span>
                </button>
<?php else: ?>
                <a href="<?= $slideshowHref ?>" class="<?= pinchard_h($slideshowClass) ?>" aria-label="Watch slideshow"></a>
<?php endif; ?>
<?php endif; ?>
<?php pinchard_nav_maps_dropdown($mapsActive); ?>
                <a class="<?= pinchard_h($infoClass) ?>" href="<?= $infoHref ?>" aria-label="About Cloudberry"></a>
            </div>
        </div>
    </nav>
<?php
}
