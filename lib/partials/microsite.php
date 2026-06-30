<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/helpers.php';

/**
 * Shared head/nav assets for PHP mini-sites under jam/, maps/, light-house/, etc.
 *
 * @param array{
 *   description?: string,
 *   og_image?: string,
 *   og_type?: string,
 *   canonical_url?: string,
 *   robots?: string,
 *   json_ld?: list<array<string, mixed>>,
 *   body_attr?: string,
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
	$description = $options['description'] ?? pinchard_cloudberry_site_description();
	$ogImage = $options['og_image'] ?? pinchard_default_og_image();
	$ogType = $options['og_type'] ?? 'website';
	$canonical = $options['canonical_url'] ?? pinchard_canonical_url();
	$jsonLd = $options['json_ld'] ?? [];
	$bodyAttr = $options['body_attr'] ?? '';
	$extraHead = $options['extra_head'] ?? '';
	$t = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
	$seoMarkup = pinchard_seo_head_markup($title, $description, [
		'og_image' => $ogImage,
		'og_type' => $ogType,
		'canonical_url' => $canonical,
		'robots' => $options['robots'] ?? null,
	]);
	?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<?= $seoMarkup ?>
    <title><?= $t ?></title>
<?= pinchard_fonts_head_html() . "\n" ?>
    <link href="<?= pinchard_h(pinchard_microsite_asset_url('vendor/bootstrap/css/bootstrap.css')) ?>" rel="stylesheet">
    <link href="<?= pinchard_h(pinchard_microsite_asset_url('css/pinchard.css')) ?>" rel="stylesheet">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= pinchard_h(pinchard_microsite_asset_url('favicon/apple-touch-icon.png')) ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= pinchard_h(pinchard_microsite_asset_url('favicon/favicon-32x32.png')) ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= pinchard_h(pinchard_microsite_asset_url('favicon/favicon-16x16.png')) ?>">
    <link rel="manifest" href="<?= pinchard_h(pinchard_microsite_asset_url('favicon/manifest.json')) ?>">
    <link rel="mask-icon" href="<?= pinchard_h(pinchard_microsite_asset_url('favicon/safari-pinned-tab.svg')) ?>" color="#5bbad5">
    <link rel="shortcut icon" href="<?= pinchard_h(pinchard_microsite_asset_url('favicon/favicon.ico')) ?>">
    <meta name="msapplication-config" content="<?= pinchard_h(pinchard_microsite_asset_url('favicon/browserconfig.xml')) ?>">
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
    <script src="<?= pinchard_h(pinchard_microsite_asset_url('js/pinchard.js')) ?>"></script>
</body>
</html>
<?php
}
