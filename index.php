<?php

declare(strict_types=1);

/**
 * Pinchard photo viewer — single image with metadata drawer.
 */

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/partials/layout.php';

if (isset($_GET['fn']) && $_GET['fn'] !== '') {
	$filenameParam = isset($_GET['filename']) && $_GET['filename'] !== ''
		? (string) $_GET['filename']
		: (string) $_GET['fn'];
	header('Location: ' . pinchard_absolute_url('/index.php', ['filename' => $filenameParam]), true, 301);
	exit;
}

try {
    $cfg = pinchard_config();

    $requestedFn = null;
    if (isset($_GET['filename']) && $_GET['filename'] !== '') {
        $requestedFn = (string) $_GET['filename'];
    }

    $array = getObjectList($cfg['s3_bucket_thumbnails']);
    usort($array, fn ($a, $b) => $a['date'] <=> $b['date']);

    if ($array === []) {
        http_response_code(503);
        exit('Photo gallery is empty or temporarily unavailable.');
    }

    $resolved = pinchard_resolve_gallery_photo($array, $requestedFn);
    $content = $resolved['photo'];
    $filename = $content['filename'];
    $datetime = $content['date'];
    $prev_filename = $resolved['prev_filename'];
    $next_filename = $resolved['next_filename'];
    $galleryContext = pinchard_gallery_context_for_photo($datetime);
    $viewerTimeline = pinchard_viewer_timeline($array, $filename, $galleryContext);
    $viewerFilenames = array_column($array, 'filename');
    $viewerCurrentIndex = 0;
    foreach ($viewerFilenames as $i => $viewerFn) {
        if ($viewerFn === $filename) {
            $viewerCurrentIndex = $i;
            break;
        }
    }

    $cdnFull = $cfg['cdn_url_full'];
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

    $dt = DateTime::createFromFormat('Y/m/d H:i:s', $datetime);
    $converted_date = $dt !== false ? $dt->format('l, F jS, Y @ g:i A') : pinchard_h($datetime);

    $pinchardMapboxToken = pinchard_env_non_empty('MAPBOX_ACCESS_TOKEN');
    $cabinCoords = pinchard_cloudberry_cabin_coords();
    $mapLat = $hasGps ? (float) $lat : $cabinCoords['lat'];
    $mapLon = $hasGps ? (float) $lon : $cabinCoords['lon'];
    $mapJe = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;

    $imageUrl = $cdnFull . $filename;
    $ogDescription = $dt !== false
        ? 'Photograph from Pinchard\'s Island — ' . $dt->format('F j, Y \a\t g:i A') . '.'
        : 'Photograph from Pinchard\'s Island.';
    $photoAlt = pinchard_photo_alt_text($datetime);
    $photoCitation = pinchard_citation_photo($filename, $datetime);

    $jsonLd = [
        [
            '@type' => 'WebSite',
            'name' => 'Cloudberry',
            'url' => pinchard_absolute_url('/index.php'),
            'description' => pinchard_cloudberry_site_description(),
        ],
    ];
    $imageObject = [
        '@type' => 'ImageObject',
        'name' => 'Cloudberry — ' . pinchard_photo_title($filename),
        'description' => $ogDescription,
        'contentUrl' => $imageUrl,
        'dateCreated' => $dt !== false ? $dt->format(DateTime::ATOM) : null,
        'creator' => [
            '@type' => 'Person',
            'name' => 'Adam Simms',
        ],
        'copyrightHolder' => [
            '@type' => 'Person',
            'name' => 'Adam Simms',
        ],
    ];
    if ($hasGps) {
        $imageObject['contentLocation'] = [
            '@type' => 'Place',
            'name' => "Pinchard's Island, Newfoundland",
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => (float) $lat,
                'longitude' => (float) $lon,
            ],
        ];
    }
    $jsonLd[] = $imageObject;

    $extraHead = '<link href="https://api.mapbox.com/mapbox-gl-js/v3.24.0/mapbox-gl.css" rel="stylesheet" />' . "\n";
    if ($prev_filename !== null && $prev_filename !== '') {
        $extraHead .= '    <link rel="prefetch" href="' . pinchard_h($cdnFull . $prev_filename) . '" as="image">' . "\n";
    }
    if ($next_filename !== null && $next_filename !== '') {
        $extraHead .= '    <link rel="prefetch" href="' . pinchard_h($cdnFull . $next_filename) . '" as="image">' . "\n";
    }

    $bodyClass = 'viewer-page';

    pinchard_layout_head('Cloudberry — ' . pinchard_photo_title($filename), [
        'description' => $ogDescription,
        'og_image' => $imageUrl,
        'og_type' => 'article',
        'body_class' => $bodyClass,
        'extra_head' => $extraHead,
        'json_ld' => $jsonLd,
    ]);

    pinchard_layout_nav(['active' => 'index']);
} catch (Throwable $e) {
    http_response_code($e instanceof \Aws\Exception\AwsException || $e instanceof RuntimeException ? 503 : 500);
    header('Content-Type: text/plain; charset=utf-8');
    if (pinchard_env_non_empty('PINCHARD_DEBUG') === '1') {
        exit($e->getMessage());
    }
    exit('Photo viewer is temporarily unavailable.');
}
?>
    <h1 class="visually-hidden"><?= pinchard_h('Cloudberry — ' . pinchard_photo_title($filename)) ?></h1>
    <div class="preview" id="photoViewer" tabindex="0" aria-label="Photograph viewer. Use arrow keys or swipe to browse. Timeline scrubber below jumps through the archive.">
<?php if ($galleryContext !== null): ?>
        <div class="photo-context-nav">
            <a href="<?= pinchard_h($galleryContext['gallery_url']) ?>">Gallery &rarr; <?= pinchard_h($galleryContext['label']) ?></a>
        </div>
<?php endif; ?>
        <div class="photo-placeholder" data-large="<?= pinchard_h($imageUrl) ?>" id="preview_image">
            <img src="images/photo/thumbnail.jpg" class="img-small" alt="<?= pinchard_h($photoAlt) ?>">
            <div style="padding-bottom: 66.6%;"></div>
        </div>

        <div class="detail_view<?= $viewerTimeline !== null ? ' has-timeline' : '' ?>" id="detailDrawer">
            <div class="detail_view-bar">
                <a href="index.php?filename=<?= pinchard_h($prev_filename ?? '') ?>" class="viewer-photo-prev<?= ($prev_filename === null || $prev_filename === '') ? ' is-hidden' : '' ?>" aria-label="Previous photograph"<?= ($prev_filename === null || $prev_filename === '') ? ' aria-hidden="true" tabindex="-1"' : '' ?>>
                    <span class="arrow left" aria-hidden="true"></span>
                </a>
                <a href="index.php?filename=<?= pinchard_h($next_filename ?? '') ?>" class="viewer-photo-next<?= ($next_filename === null || $next_filename === '') ? ' is-hidden' : '' ?>" aria-label="Next photograph"<?= ($next_filename === null || $next_filename === '') ? ' aria-hidden="true" tabindex="-1"' : '' ?>>
                    <span class="arrow right" aria-hidden="true"></span>
                </a>
<?php if ($viewerTimeline !== null): ?>
<?php
    $timelineCount = count($viewerTimeline['entries']);
    $timelinePosition = $viewerTimeline['index'] + 1;
    $timelineDate = $viewerTimeline['entries'][$viewerTimeline['index']]['d'];
    $timelineAria = pinchard_h('Photograph ' . $timelinePosition . ' of ' . $timelineCount . ', ' . $timelineDate);
?>
                <nav class="viewer-timeline" id="viewerTimeline" aria-label="Photograph timeline">
                    <span class="viewer-timeline-date" id="viewerTimelinePosition" aria-live="polite"><?= pinchard_h($timelineDate) ?></span>
                    <div class="viewer-timeline-track">
                        <input
                            type="range"
                            class="viewer-timeline-range"
                            id="viewerTimelineRange"
                            min="0"
                            max="<?= $timelineCount - 1 ?>"
                            value="<?= $viewerTimeline['index'] ?>"
                            step="1"
                            aria-label="<?= $timelineAria ?>"
                            aria-valuemin="1"
                            aria-valuemax="<?= $timelineCount ?>"
                            aria-valuenow="<?= $timelinePosition ?>"
                            aria-valuetext="<?= $timelineAria ?>"
                        >
                    </div>
                </nav>
<?php endif; ?>
                <button type="button" class="btn_arrow" id="detailToggle" aria-expanded="false" aria-controls="detailDrawerContent" aria-label="Show photograph details"></button>
            </div>
            <hr class="detail_view-divider" aria-hidden="true">
            <div class="detail_drawer-inner" id="detailDrawerContent">
            <div class="row g-0">
            <div class="col-md-5 detail_container">
                <div class="detail_content_view">
                    <div>
                        <div class="detail_rect title_rect"><img src="images/icon-number.svg" alt="" /></div>
                        <div class="title" id="viewerPhotoTitle"><?= pinchard_h(pinchard_photo_title($filename)) ?></div>
                    </div>
                    <div class="datetime_area">
                        <div class="detail_rect"><img src="images/icon-date.svg" alt="" /></div>
                        <div class="inner_data" id="viewerPhotoDate"><?= $converted_date ?></div>
                    </div>
                    <div class="inner_area">
                        <div class="detail_rect"><img src="images/icon-gopro.svg" alt="" /></div>
                        <div class="inner_data" id="viewerCameraLines"><?= implode('<br>', $cameraLines) ?></div>
                    </div>
                    <div class="inner_area">
                        <div class="detail_rect"><img src="images/icon-raspberry.svg" alt="" /></div>
                        <div class="inner_data">Photographer: Raspberry Pi 3 Model B</div>
                    </div>
                    <div class="inner_area">
                        <div class="detail_rect"><img src="images/icon-geolocation.svg" alt="" /></div>
                        <div class="inner_data" id="viewerGpsLines">
                            Position: <?= $gps_latitude_degree ?>&deg; <?= $gps_latitude_min ?>&acute; <?= $gps_latitude_sec ?>&quot; N,
                            <?= $gps_longitude_degree ?>&deg; <?= $gps_longitude_min ?>&acute; <?= $gps_longitude_sec ?>&quot; W<br>
<?php
    $alt_array = explode('/', (string) $gps_altitude);
    if (count($alt_array) === 2 && (float) $alt_array[1] !== 0.0) {
        echo 'Altitude: ' . number_format((float) $alt_array[0] / (float) $alt_array[1], 2) . ' m';
    } else {
        echo 'Altitude:';
    }
?>
                        </div>
                    </div>
                    <div class="inner_area">
                        <button type="button" class="citation-copy-btn detail-citation-copy" data-citation="<?= pinchard_h($photoCitation) ?>" aria-label="Copy citation to clipboard">Copy citation</button>
                    </div>
                </div>
            </div>
            <div class="col-md-7 mapcontainer">
<?php if ($pinchardMapboxToken !== null && str_starts_with($pinchardMapboxToken, 'pk.')): ?>
                <div id="photoMap" role="img" aria-label="Map showing photograph location"></div>
<?php elseif ($hasGps): ?>
                <p class="text-muted">Map unavailable.</p>
<?php endif; ?>
            </div>
            </div>
            </div>
        </div>
    </div>

<?php
$footerScripts = <<<'JS'
    <script>
        window.pinchardViewer = {
            cdnUrl: CDN_URL,
            filenames: VIEWER_FILENAMES,
            currentIndex: VIEWER_CURRENT_INDEX,
            fadeMs: 500,
            prevUrl: PREV_URL,
            nextUrl: NEXT_URL,
            prefetch: PRELOAD_URLS,
            currentFilename: CURRENT_FILENAME,
            timeline: TIMELINE_DATA
        };
    </script>
    <script src="js/viewer.js"></script>
JS;

$preloadUrls = [];
if ($prev_filename !== null && $prev_filename !== '') {
    $preloadUrls[] = $cdnFull . $prev_filename;
}
if ($next_filename !== null && $next_filename !== '') {
    $preloadUrls[] = $cdnFull . $next_filename;
}

$prevUrl = ($prev_filename !== null && $prev_filename !== '') ? 'index.php?filename=' . rawurlencode($prev_filename) : '';
$nextUrl = ($next_filename !== null && $next_filename !== '') ? 'index.php?filename=' . rawurlencode($next_filename) : '';

$footerScripts = str_replace('CDN_URL', json_encode($cdnFull, $mapJe), $footerScripts);
$footerScripts = str_replace('VIEWER_FILENAMES', json_encode($viewerFilenames, $mapJe), $footerScripts);
$footerScripts = str_replace('VIEWER_CURRENT_INDEX', json_encode($viewerCurrentIndex, $mapJe), $footerScripts);
$footerScripts = str_replace('PREV_URL', json_encode($prevUrl, $mapJe), $footerScripts);
$footerScripts = str_replace('NEXT_URL', json_encode($nextUrl, $mapJe), $footerScripts);
$footerScripts = str_replace('PRELOAD_URLS', json_encode($preloadUrls, $mapJe), $footerScripts);
$footerScripts = str_replace('CURRENT_FILENAME', json_encode($filename, $mapJe), $footerScripts);
$footerScripts = str_replace('TIMELINE_DATA', json_encode($viewerTimeline ?? null, $mapJe), $footerScripts);

if ($pinchardMapboxToken !== null && str_starts_with($pinchardMapboxToken, 'pk.')) {
    $footerScripts .= "\n    <script src=\"https://api.mapbox.com/mapbox-gl-js/v3.24.0/mapbox-gl.js\"></script>\n";
    $footerScripts .= "    <script>\n        document.addEventListener('DOMContentLoaded', function() {\n";
    $footerScripts .= "            var map = new mapboxgl.Map({\n";
    $footerScripts .= '                accessToken: ' . json_encode($pinchardMapboxToken, $mapJe) . ",\n";
    $footerScripts .= "                container: 'photoMap',\n";
    $footerScripts .= "                style: 'mapbox://styles/mapbox/satellite-v9',\n";
    $footerScripts .= '                center: [' . json_encode($mapLon, $mapJe) . ', ' . json_encode($mapLat, $mapJe) . "],\n";
    $footerScripts .= "                zoom: 14\n";
    $footerScripts .= "            });\n";
    $footerScripts .= '            var marker = new mapboxgl.Marker().setLngLat([' . json_encode($mapLon, $mapJe) . ', ' . json_encode($mapLat, $mapJe) . "]).addTo(map);\n";
    $footerScripts .= "            window.pinchardPhotoMap = { map: map, marker: marker };\n";
    $footerScripts .= "        });\n    </script>\n";
}

pinchard_layout_footer(['extra_scripts' => $footerScripts]);
