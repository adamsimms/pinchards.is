<?php

declare(strict_types=1);

require_once __DIR__ . '/page-shell.php';
require_once __DIR__ . '/nav.php';

/**
 * Shared layout for index, gallery, and info pages.
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
	$options['scope'] = 'root';
	pinchard_page_head($title, $options);
}

/**
 * @param array{
 *   active?: 'index'|'gallery'|'info'|null,
 *   kiosk?: bool,
 * } $options
 */
function pinchard_layout_nav(array $options = []): void
{
	pinchard_site_nav($options);
}

/** @param array{extra_scripts?: string, include_viewer?: bool} $options */
function pinchard_layout_footer(array $options = []): void
{
	pinchard_page_footer([
		'scope' => 'root',
		'include_pinchard_js' => $options['include_viewer'] ?? true,
		'extra_scripts' => $options['extra_scripts'] ?? '',
	]);
}
