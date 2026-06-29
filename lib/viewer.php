<?php

declare(strict_types=1);

/**
 * Shared viewer metadata for index.php and viewer-photo.php JSON API.
 */

/**
 * @param list<array{filename: string, date: string, show_date?: string}> $photos
 * @return array{
 *   filename: string,
 *   imageUrl: string,
 *   prevFilename: ?string,
 *   nextFilename: ?string,
 *   index: int,
 *   photoTitle: string,
 *   photoAlt: string,
 *   convertedDate: string,
 *   cameraLinesHtml: string,
 *   gpsHtml: string,
 *   citation: string,
 *   mapLat: float,
 *   mapLon: float,
 *   hasGps: bool,
 *   timelineIndex: ?int,
 * }
 */
function pinchard_viewer_photo_payload(
	array $photos,
	string $filename,
	?array $galleryContext,
	string $cdnFull,
	?array $viewerTimeline = null
): array {
	$resolved = pinchard_resolve_gallery_photo($photos, $filename);
	$content = $resolved['photo'];
	$filename = $content['filename'];
	$datetime = $content['date'];

	$exif = pinchard_read_photo_exif($filename, $cdnFull);

	$make = trim((string) ($exif['IFD0']['Make'] ?? ''));
	$model = trim((string) ($exif['IFD0']['Model'] ?? ''));
	$focal_length = $exif['EXIF']['FocalLength'] ?? '';
	$exposure_time = $exif['EXIF']['ExposureTime'] ?? '';
	$fnumber = $exif['EXIF']['FNumber'] ?? '';
	$iso_speed_ratings = $exif['EXIF']['ISOSpeedRatings'] ?? '';
	$image_width = $exif['COMPUTED']['Width'] ?? $exif['EXIF']['ExifImageWidth'] ?? '';
	$image_height = $exif['IFD0']['Height'] ?? $exif['EXIF']['ExifImageLength'] ?? '';
	$xresolution = $exif['IFD0']['XResolution'] ?? $exif['THUMBNAIL']['XResolution'] ?? '';

	$gps_latitude_degree = $gps_latitude_min = $gps_latitude_sec = '';
	$gps_longitude_degree = $gps_longitude_min = $gps_longitude_sec = '';
	$gps_altitude = $exif['GPS']['GPSAltitude'] ?? '';
	$lon = '';
	$lat = '';
	$hasGps = false;

	if (isset($exif['GPS']['GPSLatitude'], $exif['GPS']['GPSLongitude'])) {
		$gps_latitude_array = $exif['GPS']['GPSLatitude'];
		$gps_longitude_array = $exif['GPS']['GPSLongitude'];
		if (is_array($gps_latitude_array) && count($gps_latitude_array) >= 3) {
			$gps_latitude_degree = (string) pinchard_gps_rational_to_float($gps_latitude_array[0]);
			$gps_latitude_min = (string) pinchard_gps_rational_to_float($gps_latitude_array[1]);
			$gps_latitude_sec = number_format(pinchard_gps_rational_to_float($gps_latitude_array[2]), 2);
		}
		if (is_array($gps_longitude_array) && count($gps_longitude_array) >= 3) {
			$gps_longitude_degree = (string) pinchard_gps_rational_to_float($gps_longitude_array[0]);
			$gps_longitude_min = (string) pinchard_gps_rational_to_float($gps_longitude_array[1]);
			$gps_longitude_sec = number_format(pinchard_gps_rational_to_float($gps_longitude_array[2]), 2);
		}
		$latDecimal = pinchard_gps_to_decimal(
			is_array($gps_latitude_array) ? $gps_latitude_array : [],
			isset($exif['GPS']['GPSLatitudeRef']) ? (string) $exif['GPS']['GPSLatitudeRef'] : null
		);
		$lonDecimal = pinchard_gps_to_decimal(
			is_array($gps_longitude_array) ? $gps_longitude_array : [],
			isset($exif['GPS']['GPSLongitudeRef']) ? (string) $exif['GPS']['GPSLongitudeRef'] : null
		);
		if ($latDecimal !== null && $lonDecimal !== null) {
			$lat = (string) $latDecimal;
			$lon = (string) $lonDecimal;
			$hasGps = true;
		}
	}

	if (!$hasGps) {
		$gpsDefaults = pinchard_cloudberry_gps_defaults();
		$gps_latitude_degree = $gpsDefaults['latitude_degree'];
		$gps_latitude_min = $gpsDefaults['latitude_min'];
		$gps_latitude_sec = $gpsDefaults['latitude_sec'];
		$gps_longitude_degree = $gpsDefaults['longitude_degree'];
		$gps_longitude_min = $gpsDefaults['longitude_min'];
		$gps_longitude_sec = $gpsDefaults['longitude_sec'];
	}
	if ($gps_altitude === '') {
		$gps_altitude = pinchard_cloudberry_gps_defaults()['altitude'];
	}

	$cameraLines = [];
	$cameraLines[] = $make !== '' ? 'Make: ' . pinchard_h($make) : 'Make:';
	$cameraLines[] = $model !== '' ? 'Model: ' . pinchard_h($model) : 'Model:';

	$focalLine = 'Focal Length:';
	$focal_length_array = explode('/', (string) $focal_length);
	if (count($focal_length_array) === 2 && (float) $focal_length_array[1] !== 0.0) {
		$focalLine = 'Focal Length: ' . number_format((float) $focal_length_array[0] / (float) $focal_length_array[1], 2) . ' mm';
	}
	$cameraLines[] = $focalLine;

	$exposureLine = 'Exposure:';
	if ($exposure_time !== '' && $fnumber !== '' && $iso_speed_ratings !== '') {
		$exposure_array = explode('/', (string) $exposure_time);
		$fnumber_array = explode('/', (string) $fnumber);
		if (count($exposure_array) === 2 && (float) $exposure_array[0] !== 0.0 && count($fnumber_array) === 2 && (float) $fnumber_array[1] !== 0.0) {
			$exposure_value = number_format((float) $exposure_array[1] / (float) $exposure_array[0], 0);
			$fnumber_value = number_format((float) $fnumber_array[0] / (float) $fnumber_array[1], 1);
			$exposureLine = 'Exposure: 1/' . $exposure_value . ' sec, f/' . $fnumber_value . '; ISO ' . pinchard_h((string) $iso_speed_ratings);
		}
	}
	$cameraLines[] = $exposureLine;

	$imageSizeLine = 'Image Size:';
	if ($image_width !== '' && $image_height !== '') {
		$imageSizeLine = 'Image Size: ' . pinchard_h((string) $image_width) . ' x ' . pinchard_h((string) $image_height);
	}
	$cameraLines[] = $imageSizeLine;

	$resolutionLine = 'Resolution:';
	$resolution_array = explode('/', (string) $xresolution);
	if (count($resolution_array) === 2 && (float) $resolution_array[1] !== 0.0) {
		$resolutionLine = 'Resolution: ' . number_format((float) $resolution_array[0] / (float) $resolution_array[1], 2) . ' pixels per inch';
	}
	$cameraLines[] = $resolutionLine;

	$dt = DateTime::createFromFormat('Y/m/d H:i:s', $datetime);
	$converted_date = $dt !== false ? $dt->format('l, F jS, Y @ g:i A') : pinchard_h($datetime);

	$alt_array = explode('/', (string) $gps_altitude);
	if (count($alt_array) === 2 && (float) $alt_array[1] !== 0.0) {
		$altitudeLine = 'Altitude: ' . number_format((float) $alt_array[0] / (float) $alt_array[1], 2) . ' m';
	} else {
		$altitudeLine = 'Altitude:';
	}

	$gpsHtml = 'Position: ' . $gps_latitude_degree . '&deg; ' . $gps_latitude_min . '&acute; ' . $gps_latitude_sec . '&quot; N, '
		. $gps_longitude_degree . '&deg; ' . $gps_longitude_min . '&acute; ' . $gps_longitude_sec . '&quot; W<br>'
		. $altitudeLine;

	$index = 0;
	foreach ($photos as $i => $photo) {
		if ($photo['filename'] === $filename) {
			$index = $i;
			break;
		}
	}

	$timelineIndex = null;
	if ($viewerTimeline !== null) {
		foreach ($viewerTimeline['entries'] as $i => $entry) {
			if ($entry['f'] === $filename) {
				$timelineIndex = $i;
				break;
			}
		}
	}

	$cabinCoords = pinchard_cloudberry_cabin_coords();
	$mapLat = $hasGps ? (float) $lat : $cabinCoords['lat'];
	$mapLon = $hasGps ? (float) $lon : $cabinCoords['lon'];

	return [
		'filename' => $filename,
		'imageUrl' => $cdnFull . $filename,
		'prevFilename' => $resolved['prev_filename'],
		'nextFilename' => $resolved['next_filename'],
		'index' => $index,
		'photoTitle' => pinchard_photo_title($filename),
		'photoAlt' => pinchard_photo_alt_text($datetime),
		'convertedDate' => $converted_date,
		'cameraLinesHtml' => implode('<br>', $cameraLines),
		'gpsHtml' => $gpsHtml,
		'citation' => pinchard_citation_photo($filename, $datetime),
		'mapLat' => $mapLat,
		'mapLon' => $mapLon,
		'hasGps' => $hasGps,
		'timelineIndex' => $timelineIndex,
	];
}
