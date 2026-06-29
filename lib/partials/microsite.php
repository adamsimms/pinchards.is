<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/helpers.php';

/**
 * Shared head/nav assets for PHP mini-sites under jam/, maps/, light-house/, etc.
 *
 * @param array{
 *   body_attr?: string,
 *   google_fonts?: bool,
 *   font_awesome?: bool,
 *   extra_head?: string,
 *   base_path?: string,
 * } $options
 */
function pinchard_microsite_asset_base(array $options = []): string
{
	return rtrim($options['base_path'] ?? '..', '/');
}

/** Root-relative asset URL with cache-busting from file mtime. */
function pinchard_microsite_asset_url(string $path): string
{
	$path = '/' . ltrim($path, '/');
	$root = dirname(__DIR__, 2);
	$full = $root . $path;
	$version = is_file($full) ? (string) filemtime($full) : '1';

	return $path . '?v=' . rawurlencode($version);
}

function pinchard_microsite_head(string $title, array $options = []): void
{
	$bodyAttr = $options['body_attr'] ?? '';
	$googleFonts = $options['google_fonts'] ?? false;
	$fontAwesome = $options['font_awesome'] ?? false;
	$extraHead = $options['extra_head'] ?? '';
	$t = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
	?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title><?= $t ?></title>
    <link href="<?= pinchard_h(pinchard_microsite_asset_url('vendor/bootstrap/css/bootstrap.css')) ?>" rel="stylesheet">
<?php if ($fontAwesome): ?>
    <link href="<?= pinchard_h(pinchard_microsite_asset_url('vendor/font-awesome/css/font-awesome.css')) ?>" rel="stylesheet" type="text/css">
<?php endif; ?>
<?php if ($googleFonts): ?>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Merriweather:400,300,300italic,400italic,700,700italic,900,900italic" rel="stylesheet">
<?php endif; ?>
    <link href="<?= pinchard_h(pinchard_microsite_asset_url('css/pinchard.css')) ?>" rel="stylesheet">
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.js"></script>
    <![endif]-->
    <link rel="apple-touch-icon" sizes="180x180" href="<?= pinchard_h(pinchard_microsite_asset_url('favicon/apple-touch-icon.png')) ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= pinchard_h(pinchard_microsite_asset_url('favicon/favicon-32x32.png')) ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= pinchard_h(pinchard_microsite_asset_url('favicon/favicon-16x16.png')) ?>">
    <link rel="manifest" href="<?= pinchard_h(pinchard_microsite_asset_url('favicon/manifest.json')) ?>">
    <link rel="mask-icon" href="<?= pinchard_h(pinchard_microsite_asset_url('favicon/safari-pinned-tab.svg')) ?>" color="#5bbad5">
    <link rel="shortcut icon" href="<?= pinchard_h(pinchard_microsite_asset_url('favicon/favicon.ico')) ?>">
    <meta name="msapplication-config" content="<?= pinchard_h(pinchard_microsite_asset_url('favicon/browserconfig.xml')) ?>">
    <meta name="theme-color" content="#ffffff">
<?php
	if ($extraHead !== '') {
		echo $extraHead;
	}
?>
    <script src="<?= pinchard_h(pinchard_microsite_asset_url('vendor/jquery/jquery.js')) ?>"></script>
</head>
<body<?= $bodyAttr !== '' ? ' ' . $bodyAttr : '' ?>>
<?php
}

/**
 * Mini-site nav — title on the left, gallery + info on the right (matches root layout).
 *
 * @param array{brand_href?: string, base_path?: string} $options
 */
function pinchard_microsite_nav(string $title, array $options = []): void
{
	$brandHref = $options['brand_href'] ?? '#';
	$t = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
	$href = htmlspecialchars($brandHref, ENT_QUOTES, 'UTF-8');
	$galleryHref = pinchard_h(pinchard_microsite_asset_url('gallery.php'));
	$infoHref = pinchard_h(pinchard_microsite_asset_url('info.php'));
	?>
    <nav id="mainNav" class="navbar navbar-default fixed-top" aria-label="Site">
        <div class="nav-bar-inner">
            <div class="nav-bar-start">
                <a href="<?= $href ?>" class="title-brand"><?= $t ?></a>
            </div>
            <div class="nav-bar-end">
                <a href="<?= $galleryHref ?>" class="link-to-gallery nav_cloudberry" aria-label="Browse photo gallery"></a>
                <a class="nav_info" href="<?= $infoHref ?>" aria-label="About Cloudberry"></a>
            </div>
        </div>
    </nav>
<?php
}

/**
 * @param array{base_path?: string} $options
 */
function pinchard_microsite_scripts_footer(array $options = []): void
{
	?>
    <script src="<?= pinchard_h(pinchard_microsite_asset_url('vendor/bootstrap/js/bootstrap.bundle.js')) ?>"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.js"></script>
    <script src="<?= pinchard_h(pinchard_microsite_asset_url('js/pinchard.js')) ?>"></script>
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
