<?php

declare(strict_types=1);

/**
 * Core site settings (S3 + CloudFront). Edit here instead of scattering URLs in PHP templates.
 */
return [
	's3_bucket_full' => 'shutter-island',
	's3_bucket_thumbnails' => 'shutter-island-thumbnails',
	'cdn_url_full' => 'https://d3kq73uimqeic8.cloudfront.net/',
	'cdn_url_thumbnails' => 'https://d35wkpjsrmtk40.cloudfront.net/',
];
