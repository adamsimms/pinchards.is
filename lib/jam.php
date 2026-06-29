<?php

declare(strict_types=1);

/**
 * Jam slideshow: full-resolution S3 listing via getObjectList() and config CDN/bucket.
 *
 * @return array{0: string, 1: list<array{filename: string, date: string, show_date: string}>}
 */
function pinchard_jam_photo_list(): array
{
	$cfg = pinchard_config();
	$list = getObjectList($cfg['s3_bucket_full']);
	$cdn = $cfg['cdn_url_full'];
	usort($list, fn ($a, $b) => $a['date'] <=> $b['date']);
	return [$cdn, $list];
}
