<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/helpers.php';

/**
 * Asset URL for root pages or mini-sites (cache-busted under subdirectories).
 *
 * @param array{scope?: 'root'|'microsite'} $options
 */
function pinchard_page_asset_url(string $path, array $options = []): string
{
	$path = '/' . ltrim($path, '/');
	$scope = $options['scope'] ?? 'root';
	if ($scope !== 'microsite') {
		return $path;
	}

	$root = dirname(__DIR__, 2);
	$full = $root . $path;
	$version = is_file($full) ? (string) filemtime($full) : '1';

	return $path . '?v=' . rawurlencode($version);
}

/**
 * @param array{
 *   scope?: 'root'|'microsite',
 *   description?: string,
 *   og_image?: string,
 *   og_type?: string,
 *   canonical_url?: string,
 *   json_ld?: list<array<string, mixed>>,
 *   robots?: string,
 *   extra_head?: string,
 *   body_class?: string,
 *   body_id?: string,
 *   body_attr?: string,
 * } $options
 */
function pinchard_page_head(string $title, array $options = []): void
{
	$scope = $options['scope'] ?? 'root';
	$description = $options['description'] ?? pinchard_cloudberry_site_description();
	$ogImage = $options['og_image'] ?? pinchard_default_og_image();
	$ogType = $options['og_type'] ?? 'website';
	$canonical = $options['canonical_url'] ?? pinchard_canonical_url();
	$jsonLd = $options['json_ld'] ?? [];
	$extraHead = $options['extra_head'] ?? '';
	$t = pinchard_h($title);
	$seoMarkup = pinchard_seo_head_markup($title, $description, [
		'og_image' => $ogImage,
		'og_type' => $ogType,
		'canonical_url' => $canonical,
		'robots' => $options['robots'] ?? null,
	]);

	$bootstrapCss = pinchard_h(pinchard_page_asset_url('vendor/bootstrap/css/bootstrap.css', ['scope' => $scope]));
	$siteCss = pinchard_h(pinchard_page_asset_url('css/pinchard.css', ['scope' => $scope]));
	$faviconBase = fn (string $file): string => pinchard_h(pinchard_page_asset_url('favicon/' . $file, ['scope' => $scope]));

	if (isset($options['body_attr'])) {
		$bodyOpen = '<body ' . $options['body_attr'] . '>';
	} else {
		$bodyId = pinchard_h($options['body_id'] ?? 'page-top');
		$bodyClass = $options['body_class'] ?? '';
		$bodyOpen = '<body id="' . $bodyId . '"';
		if ($bodyClass !== '') {
			$bodyOpen .= ' class="' . pinchard_h($bodyClass) . '"';
		}
		$bodyOpen .= '>';
	}
	?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<?= $seoMarkup ?>
    <title><?= $t ?></title>
<?= pinchard_fonts_head_html() . "\n" ?>
    <link href="<?= $bootstrapCss ?>" rel="stylesheet">
    <link href="<?= $siteCss ?>" rel="stylesheet">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= $faviconBase('apple-touch-icon.png') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= $faviconBase('favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= $faviconBase('favicon-16x16.png') ?>">
    <link rel="manifest" href="<?= $faviconBase('manifest.json') ?>">
    <link rel="mask-icon" href="<?= $faviconBase('safari-pinned-tab.svg') ?>" color="#5bbad5">
    <link rel="shortcut icon" href="<?= $faviconBase('favicon.ico') ?>">
    <meta name="msapplication-config" content="<?= $faviconBase('browserconfig.xml') ?>">
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
<?= $bodyOpen ?>
<?php
}

/**
 * @param array{
 *   scope?: 'root'|'microsite',
 *   include_pinchard_js?: bool,
 *   include_gsap?: bool,
 *   extra_scripts?: string,
 * } $options
 */
function pinchard_page_footer(array $options = []): void
{
	$scope = $options['scope'] ?? 'root';
	$includePinchardJs = $options['include_pinchard_js'] ?? true;
	$includeGsap = $options['include_gsap'] ?? true;
	$extraScripts = $options['extra_scripts'] ?? '';
	if ($includeGsap) {
		$gsapJs = pinchard_h(pinchard_page_asset_url('vendor/gsap/gsap.min.js', ['scope' => $scope]));
		$scrollTriggerJs = pinchard_h(pinchard_page_asset_url('vendor/gsap/ScrollTrigger.min.js', ['scope' => $scope]));
		$motionJs = pinchard_h(pinchard_page_asset_url('js/gsap-motion.js', ['scope' => $scope]));
		echo '    <script src="' . $gsapJs . '"></script>' . "\n";
		echo '    <script src="' . $scrollTriggerJs . '"></script>' . "\n";
		echo '    <script src="' . $motionJs . '"></script>' . "\n";
	}
	if ($includePinchardJs) {
		$pinchardJs = pinchard_h(pinchard_page_asset_url('js/pinchard.js', ['scope' => $scope]));
		echo '    <script src="' . $pinchardJs . '"></script>' . "\n";
	}
	if ($extraScripts !== '') {
		echo $extraScripts;
	}
	?>
</body>
</html>
<?php
}

/**
 * Inline config + external slideshow.js for root or mini-site pages.
 *
 * @param array{
 *   display: float,
 *   fade: float,
 *   images: list<array<string, mixed>>,
 *   cdnurl: string,
 *   timeline?: array<string, mixed>|null,
 *   startIndex?: int,
 *   scope?: 'root'|'microsite',
 * } $config
 */
function pinchard_slideshow_scripts(array $config): string
{
	$je = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
	$scope = $config['scope'] ?? 'root';
	$payload = [
		'display' => $config['display'] * 1000,
		'fade' => $config['fade'] * 1000,
		'images' => $config['images'],
		'timeline' => $config['timeline'] ?? null,
		'cdnurl' => $config['cdnurl'],
		'startIndex' => $config['startIndex'] ?? 0,
	];
	$jsUrl = pinchard_h(pinchard_page_asset_url('js/slideshow.js', ['scope' => $scope]));

	return '<script>window.pinchardSlideshow = ' . json_encode($payload, $je) . ";</script>\n"
		. '    <script src="' . $jsUrl . '"></script>';
}
