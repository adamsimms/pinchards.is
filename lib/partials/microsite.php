<?php

declare(strict_types=1);

require_once __DIR__ . '/page-shell.php';

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
 *   body_class?: string,
 *   body_id?: string,
 *   extra_head?: string,
 * } $options
 */
function pinchard_microsite_head(string $title, array $options = []): void
{
	$options['scope'] = 'microsite';
	pinchard_page_head($title, $options);
}

/** Root-relative asset URL with cache-busting from file mtime. */
function pinchard_microsite_asset_url(string $path): string
{
	return pinchard_page_asset_url($path, ['scope' => 'microsite']);
}

/**
 * @param array{extra_scripts?: string, include_pinchard_js?: bool} $options
 */
function pinchard_microsite_scripts_footer(array $options = []): void
{
	pinchard_page_footer([
		'scope' => 'microsite',
		'include_pinchard_js' => $options['include_pinchard_js'] ?? true,
		'extra_scripts' => $options['extra_scripts'] ?? '',
	]);
}
