<?php

declare(strict_types=1);

/**
 * Jam slideshows: shared S3 listing via getObjectList() and config CDN/bucket pairing.
 *
 * @param 'thumbnails'|'full' $source thumbnails => gallery CDN + thumbnail bucket; full => full-res
 * @return array{0: string, 1: list<array{filename: string, date: string, show_date: string}>}
 */
function pinchard_jam_photo_list(string $source): array
{
	$cfg = pinchard_config();
	if ($source === 'thumbnails') {
		$list = getObjectList($cfg['s3_bucket_thumbnails']);
		$cdn = $cfg['cdn_url_thumbnails'];
	} elseif ($source === 'full') {
		$list = getObjectList($cfg['s3_bucket_full']);
		$cdn = $cfg['cdn_url_full'];
	} else {
		throw new InvalidArgumentException('pinchard_jam_photo_list: source must be thumbnails or full');
	}
	usort($list, fn($a, $b) => $a['date'] <=> $b['date']);
	return [$cdn, $list];
}
