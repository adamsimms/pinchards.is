<?php

declare(strict_types=1);

/**
 * Shared layout for index, gallery, info, and slideshow pages.
 *
 * @param array{
 *   description?: string,
 *   og_image?: string,
 *   og_type?: string,
 *   canonical_url?: string,
 *   json_ld?: list<array<string, mixed>>,
 *   extra_head?: string,
 *   body_class?: string,
 *   body_id?: string,
 * } $options
 */
function pinchard_layout_head(string $title, array $options = []): void
{
	$description = $options['description'] ?? pinchard_cloudberry_site_description();
	$ogImage = $options['og_image'] ?? 'https://www.pinchards.is/images/info/pano.jpg';
	$ogType = $options['og_type'] ?? 'website';
	$canonical = $options['canonical_url'] ?? pinchard_canonical_url();
	$jsonLd = $options['json_ld'] ?? [];
	$extraHead = $options['extra_head'] ?? '';
	$bodyClass = $options['body_class'] ?? '';
	$bodyId = $options['body_id'] ?? 'page-top';
	$t = pinchard_h($title);
	$d = pinchard_h($description);
	$og = pinchard_h($ogImage);
	$canonicalEsc = pinchard_h($canonical);
	?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= $d ?>">
    <meta name="author" content="Adam Simms &amp; Angela Gabereaux">
    <meta property="og:title" content="<?= $t ?>">
    <meta property="og:description" content="<?= $d ?>">
    <meta property="og:image" content="<?= $og ?>">
    <meta property="og:type" content="<?= pinchard_h($ogType) ?>">
    <meta property="og:url" content="<?= $canonicalEsc ?>">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="canonical" href="<?= $canonicalEsc ?>">
    <title><?= $t ?></title>
    <link href="vendor/bootstrap/css/bootstrap.css" rel="stylesheet">
    <link href="css/pinchard.css" rel="stylesheet">
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">
    <link rel="manifest" href="/favicon/manifest.json">
    <link rel="mask-icon" href="/favicon/safari-pinned-tab.svg" color="#5bbad5">
    <link rel="shortcut icon" href="/favicon/favicon.ico">
    <meta name="msapplication-config" content="/favicon/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">
<?php
	$jsonLdScript = pinchard_json_ld_script($jsonLd);
	if ($jsonLdScript !== '') {
		echo '    ' . $jsonLdScript . "\n";
	}
	if ($extraHead !== '') {
		echo $extraHead;
	}
?>
</head>
<body id="<?= pinchard_h($bodyId) ?>"<?= $bodyClass !== '' ? ' class="' . pinchard_h($bodyClass) . '"' : '' ?>>
<?php
}

/**
 * @param array{
 *   active?: 'index'|'gallery'|'info'|'slideshow'|null,
 *   show_slideshow?: bool,
 * } $options
 */
function pinchard_layout_nav(array $options = []): void
{
	$active = $options['active'] ?? null;
	$showSlideshow = $options['show_slideshow'] ?? true;
	$galleryClass = 'link-to-gallery nav_cloudberry' . ($active === 'gallery' ? ' active' : '');
	$infoClass = 'nav_info' . ($active === 'info' ? ' active' : '');
	$slideshowClass = 'nav_slideshow' . ($active === 'slideshow' ? ' active' : '');
	?>
    <nav id="mainNav" class="navbar navbar-default fixed-top" aria-label="Site">
        <div class="nav-bar-inner">
            <div class="nav-bar-start">
                <a href="index.php" class="title-brand"<?= $active === 'index' ? ' aria-current="page"' : '' ?>>Cloudberry</a>
            </div>
            <div class="nav-bar-end">
                <a href="gallery.php" class="<?= pinchard_h($galleryClass) ?>" aria-label="Browse photo gallery"></a>
<?php if ($showSlideshow): ?>
<?php if ($active === 'slideshow'): ?>
                <button type="button" class="nav-slideshow-control" id="navSlideshowToggle" aria-label="Pause slideshow">
                    <span class="nav-slideshow-icons" aria-hidden="true">
                        <span class="nav-slideshow-icon nav-slideshow-icon--pause"></span>
                        <span class="nav-slideshow-icon nav-slideshow-icon--play"></span>
                    </span>
                </button>
<?php else: ?>
                <a href="slideshow.php" class="<?= pinchard_h($slideshowClass) ?>" aria-label="Watch slideshow"></a>
<?php endif; ?>
<?php endif; ?>
                <a class="<?= pinchard_h($infoClass) ?>" href="info.php" aria-label="About Cloudberry"></a>
            </div>
        </div>
    </nav>
<?php
}

/** @param array{extra_scripts?: string, include_viewer?: bool} $options */
function pinchard_layout_footer(array $options = []): void
{
	$extraScripts = $options['extra_scripts'] ?? '';
	$includeViewer = $options['include_viewer'] ?? true;
	?>
    <script src="vendor/jquery/jquery.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.js"></script>
<?php if ($includeViewer): ?>
    <script src="js/pinchard.js"></script>
<?php endif; ?>
<?php
	if ($extraScripts !== '') {
		echo $extraScripts;
	}
?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-G1XKSQNT5M"></script>
    <script>
         window.dataLayer = window.dataLayer || [];
         function gtag(){dataLayer.push(arguments);}
         gtag('js', new Date());
         gtag('config', 'G-G1XKSQNT5M');
    </script>
</body>
</html>
<?php
}
