<?php

declare(strict_types=1);

/**
 * Jam slideshow page body + scripts. Required variables in scope before include:
 * - $jam_page_title (string)
 * - $jam_layout ('crop'|'fill') crop = centered crop; fill = full-bleed projection
 * - $display, $fade (float-ish from GET)
 * - $cdnurl (string)
 * - $array (photo rows from pinchard_jam_photo_list)
 * - $jam_start (int, optional) first index (?start=)
 */
require_once __DIR__ . '/microsite.php';

$jam_start = isset($jam_start) ? (int) $jam_start : 0;
$layoutFill = isset($jam_layout) && $jam_layout === 'fill';
$jamBodyClass = 'jam-page ' . ($layoutFill ? 'jam-page--fill' : 'jam-page--crop');

$jamDescription = 'Fullscreen exhibition slideshow from the Cloudberry archive — for projection and direct-link playback only.';
pinchard_microsite_head($jam_page_title, [
	'description' => $jamDescription,
	'canonical_url' => pinchard_absolute_url('/jam/'),
	'robots' => 'noindex, nofollow',
	'body_attr' => 'class="' . pinchard_h($jamBodyClass) . '"',
]);

$slideshowFadeStyle = '--slideshow-fade: ' . pinchard_h((string) $fade) . 's;';
?>
    <div class="slideshow-shell jam-slideshow-shell">
        <div class="slideshow-viewport" id="slideshow" style="<?= $slideshowFadeStyle ?>" aria-live="polite" aria-label="Exhibition slideshow"></div>
    </div>

<?php
$footerScripts = pinchard_slideshow_scripts([
	'display' => (float) $display,
	'fade' => (float) $fade,
	'images' => $array,
	'cdnurl' => $cdnurl,
	'startIndex' => $jam_start,
	'scope' => 'microsite',
]);

pinchard_microsite_scripts_footer(['extra_scripts' => $footerScripts]);
