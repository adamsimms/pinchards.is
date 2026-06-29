<?php

declare(strict_types=1);

require_once __DIR__ . '/nav.php';

/**
 * Shared layout for index, gallery, info, and slideshow pages.
 *
 * @param array{
 *   description?: string,
 *   og_image?: string,
 *   og_type?: string,
 *   canonical_url?: string,
 *   json_ld?: list<array<string, mixed>>,
 *   robots?: string,
 *   extra_head?: string,
 *   body_class?: string,
 *   body_id?: string,
 * } $options
 */
function pinchard_layout_head(string $title, array $options = []): void
{
	$description = $options['description'] ?? pinchard_cloudberry_site_description();
	$ogImage = $options['og_image'] ?? pinchard_default_og_image();
	$ogType = $options['og_type'] ?? 'website';
	$canonical = $options['canonical_url'] ?? pinchard_canonical_url();
	$jsonLd = $options['json_ld'] ?? [];
	$extraHead = $options['extra_head'] ?? '';
	$bodyClass = $options['body_class'] ?? '';
	$bodyId = $options['body_id'] ?? 'page-top';
	$t = pinchard_h($title);
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
<?= $seoMarkup ?>
    <title><?= $t ?></title>
<?= pinchard_fonts_head_html() . "\n" ?>
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
	pinchard_site_nav($options);
}

/** @param array{extra_scripts?: string, include_viewer?: bool} $options */
function pinchard_layout_footer(array $options = []): void
{
	$extraScripts = $options['extra_scripts'] ?? '';
	$includeViewer = $options['include_viewer'] ?? true;
	?>
<?php if ($includeViewer): ?>
    <script src="js/pinchard.js"></script>
<?php endif; ?>
<?php
	if ($extraScripts !== '') {
		echo $extraScripts;
	}
	$analytics = pinchard_analytics_footer_html();
	if ($analytics !== '') {
		echo $analytics . "\n";
	}
?>
</body>
</html>
<?php
}
