<?php

declare(strict_types=1);

/**
 * Pinchard photo viewer — single image with metadata drawer.
 */

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/partials/layout.php';

try {
    $cfg = pinchard_config();

    $requestedFn = null;
    if (isset($_GET['filename']) && $_GET['filename'] !== '') {
        $requestedFn = (string) $_GET['filename'];
    } elseif (isset($_GET['fn']) && $_GET['fn'] !== '') {
        $requestedFn = (string) $_GET['fn'];
    }

    $array = getObjectList($cfg['s3_bucket_full']);
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

    $tmpPath = pinchard_exif_tmp_path();
    if (!pinchard_exif_tmp_matches_key($filename)) {
        $s3->getObject([
            'Bucket' => $cfg['s3_bucket_full'],
            'Key' => $filename,
            'SaveAs' => $tmpPath,
        ]);
        pinchard_exif_tmp_record_key($filename);
    }

    $exif = exif_read_data($tmpPath, 0, true);

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
        $gps_latitude_degree = explode('/', $gps_latitude_array[0])[0];
        $gps_latitude_min = explode('/', $gps_latitude_array[1])[0];
        $gps_latitude_sec = number_format(explode('/', $gps_latitude_array[2])[0] / explode('/', $gps_latitude_array[2])[1], 2);
        $gps_longitude_degree = explode('/', $gps_longitude_array[0])[0];
        $gps_longitude_min = explode('/', $gps_longitude_array[1])[0];
        $gps_longitude_sec = number_format(explode('/', $gps_longitude_array[2])[0] / explode('/', $gps_longitude_array[2])[1], 2);
        $lon = (string) getGps($exif['GPS']['GPSLongitude'], $exif['GPS']['GPSLongitudeRef']);
        $lat = (string) getGps($exif['GPS']['GPSLatitude'], $exif['GPS']['GPSLatitudeRef']);
        $hasGps = true;
    }

    $cameraLines = [];
    if ($make !== '') {
        $cameraLines[] = 'Make: ' . pinchard_h($make);
    }
    if ($model !== '') {
        $cameraLines[] = 'Model: ' . pinchard_h($model);
    }
    if ($focal_length !== '') {
        $focal_length_array = explode('/', (string) $focal_length);
        if (count($focal_length_array) === 2 && (float) $focal_length_array[1] !== 0.0) {
            $cameraLines[] = 'Focal Length: ' . number_format((float) $focal_length_array[0] / (float) $focal_length_array[1], 2) . ' mm';
        }
    }
    if ($exposure_time !== '' && $fnumber !== '' && $iso_speed_ratings !== '') {
        $exposure_array = explode('/', (string) $exposure_time);
        $fnumber_array = explode('/', (string) $fnumber);
        if (count($exposure_array) === 2 && (float) $exposure_array[0] !== 0.0 && count($fnumber_array) === 2 && (float) $fnumber_array[1] !== 0.0) {
            $exposure_value = number_format((float) $exposure_array[1] / (float) $exposure_array[0], 0);
            $fnumber_value = number_format((float) $fnumber_array[0] / (float) $fnumber_array[1], 1);
            $cameraLines[] = 'Exposure: 1/' . $exposure_value . ' sec, f/' . $fnumber_value . '; ISO ' . pinchard_h((string) $iso_speed_ratings);
        }
    }
    if ($image_width !== '' && $image_height !== '') {
        $cameraLines[] = 'Image Size: ' . pinchard_h((string) $image_width) . ' x ' . pinchard_h((string) $image_height);
    }
    if ($xresolution !== '') {
        $resolution_array = explode('/', (string) $xresolution);
        if (count($resolution_array) === 2 && (float) $resolution_array[1] !== 0.0) {
            $cameraLines[] = 'Resolution: ' . number_format((float) $resolution_array[0] / (float) $resolution_array[1], 2) . ' pixels per inch';
        }
    }

    $dt = DateTime::createFromFormat('Y/m/d H:i:s', $datetime);
    $converted_date = $dt !== false ? $dt->format('l, F jS, Y @ g:i A') : pinchard_h($datetime);

    $pinchardMapboxToken = pinchard_env_non_empty('MAPBOX_ACCESS_TOKEN');
    $mapLat = $hasGps ? (float) $lat : 49.2025694;
    $mapLon = $hasGps ? (float) $lon : -53.48586388888953;
    $mapJe = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;

    $cdnFull = $cfg['cdn_url_full'];
    $imageUrl = $cdnFull . $filename;
    $ogDescription = $dt !== false
        ? 'Photograph from Pinchard\'s Island — ' . $dt->format('F j, Y \a\t g:i A') . '.'
        : 'Photograph from Pinchard\'s Island.';

    $extraHead = '<script src="vendor/exifjs/exif.js"></script>' . "\n";
    $extraHead .= '    <link href="https://api.mapbox.com/mapbox-gl-js/v3.24.0/mapbox-gl.css" rel="stylesheet" />' . "\n";
    if ($prev_filename !== null && $prev_filename !== '') {
        $extraHead .= '    <link rel="prefetch" href="' . pinchard_h($cdnFull . $prev_filename) . '" as="image">' . "\n";
    }
    if ($next_filename !== null && $next_filename !== '') {
        $extraHead .= '    <link rel="prefetch" href="' . pinchard_h($cdnFull . $next_filename) . '" as="image">' . "\n";
    }

    pinchard_layout_head("Pinchard's Island — " . pinchard_photo_title($filename), [
        'description' => $ogDescription,
        'og_image' => $imageUrl,
        'og_type' => 'article',
        'body_class' => 'viewer-page',
        'extra_head' => $extraHead,
    ]);

    pinchard_layout_nav([
        'active' => 'index',
        'prev_filename' => $prev_filename,
        'next_filename' => $next_filename,
    ]);
} catch (RuntimeException | \Aws\Exception\AwsException $e) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=utf-8');
    if (pinchard_env_non_empty('PINCHARD_DEBUG') === '1') {
        exit($e->getMessage());
    }
    exit('Photo viewer is temporarily unavailable.');
}

function getGps(array $exifCoord, string $hemi): float
{
    $degrees = count($exifCoord) > 0 ? gps2Num($exifCoord[0]) : 0;
    $minutes = count($exifCoord) > 1 ? gps2Num($exifCoord[1]) : 0;
    $seconds = count($exifCoord) > 2 ? gps2Num($exifCoord[2]) : 0;
    $flip = ($hemi === 'W' || $hemi === 'S') ? -1 : 1;

    return $flip * ($degrees + $minutes / 60 + $seconds / 3600);
}

function gps2Num(string $coordPart): float
{
    $parts = explode('/', $coordPart);
    if (count($parts) <= 0) {
        return 0;
    }
    if (count($parts) === 1) {
        return (float) $parts[0];
    }

    return floatval($parts[0]) / floatval($parts[1]);
}
?>
    <div class="preview" id="photoViewer" tabindex="0" aria-label="Photograph viewer. Use arrow keys or swipe to browse.">
<?php if ($galleryContext !== null): ?>
        <div class="photo-context-nav">
            <a href="<?= pinchard_h($galleryContext['gallery_url']) ?>">Gallery &rarr; <?= pinchard_h($galleryContext['label']) ?></a>
        </div>
<?php endif; ?>
        <div class="placeholder" data-large="<?= pinchard_h($imageUrl) ?>" id="preview_image">
            <img src="images/photo/thumbnail.jpg" class="img-small" alt="">
            <div style="padding-bottom: 66.6%;"></div>
        </div>

        <div class="detail_view" id="detailDrawer">
            <button type="button" class="btn_arrow" id="detailToggle" aria-expanded="false" aria-controls="detailDrawerContent" aria-label="Show photograph details"></button>
            <div class="detail_drawer-inner" id="detailDrawerContent">
            <div class="row g-0">
            <div class="col-md-5 detail_container">
                <div class="detail_content_view">
                    <div>
                        <div class="detail_rect title_rect"><img src="images/icon-number.svg" alt="" /></div>
                        <div class="title"><?= pinchard_h(pinchard_photo_title($filename)) ?></div>
                    </div>
                    <div class="datetime_area">
                        <div class="detail_rect"><img src="images/icon-date.svg" alt="" /></div>
                        <div class="inner_data"><?= $converted_date ?></div>
                    </div>
<?php if ($cameraLines !== []): ?>
                    <div class="inner_area">
                        <div class="detail_rect"><img src="images/icon-gopro.svg" alt="" /></div>
                        <div class="inner_data"><?= implode('<br>', $cameraLines) ?></div>
                    </div>
<?php endif; ?>
                    <div class="inner_area">
                        <div class="detail_rect"><img src="images/icon-raspberry.svg" alt="" /></div>
                        <div class="inner_data">Photographer: Raspberry Pi 3 Model B</div>
                    </div>
<?php if ($hasGps): ?>
                    <div class="inner_area">
                        <div class="detail_rect"><img src="images/icon-geolocation.svg" alt="" /></div>
                        <div class="inner_data">
                            Position: <?= $gps_latitude_degree ?>&deg; <?= $gps_latitude_min ?>&acute; <?= $gps_latitude_sec ?>&quot; N,
                            <?= $gps_longitude_degree ?>&deg; <?= $gps_longitude_min ?>&acute; <?= $gps_longitude_sec ?>&quot; W<br>
<?php
    if ($gps_altitude !== '') {
        $alt_array = explode('/', (string) $gps_altitude);
        if (count($alt_array) === 2 && (float) $alt_array[1] !== 0.0) {
            echo 'Altitude: ' . number_format((float) $alt_array[0] / (float) $alt_array[1], 2) . ' m';
        }
    }
?>
                        </div>
                    </div>
<?php endif; ?>
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
            prevUrl: PREV_URL,
            nextUrl: NEXT_URL,
            prefetch: PRELOAD_URLS
        };
    </script>
    <script>
        (function() {
            var placeholder = document.querySelector('.placeholder'),
                small = placeholder.querySelector('.img-small');
            var img = new Image();
            img.src = small.src;
            img.onload = function() { small.classList.add('loaded'); };
            var imgLarge = new Image();
            imgLarge.src = placeholder.dataset.large;
            imgLarge.onload = function() { imgLarge.classList.add('loaded'); };
            placeholder.appendChild(imgLarge);

            if (window.pinchardViewer.prefetch) {
                window.pinchardViewer.prefetch.forEach(function(url) {
                    var p = new Image();
                    p.src = url;
                });
            }
        })();
    </script>
    <script>
        (function() {
            var drawer = document.getElementById('detailDrawer');
            var toggle = document.getElementById('detailToggle');
            if (!drawer || !toggle) return;

            toggle.addEventListener('click', function() {
                var open = drawer.classList.toggle('open');
                toggle.classList.toggle('down_arrow', open);
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                toggle.setAttribute('aria-label', open ? 'Hide photograph details' : 'Show photograph details');
            });

            document.addEventListener('keydown', function(e) {
                if (e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA')) return;
                var v = window.pinchardViewer;
                if (e.key === 'ArrowLeft' && v.prevUrl) {
                    window.location.href = v.prevUrl;
                } else if (e.key === 'ArrowRight' && v.nextUrl) {
                    window.location.href = v.nextUrl;
                }
            });

            var viewer = document.getElementById('photoViewer');
            var touchStartX = 0;
            viewer.addEventListener('touchstart', function(e) {
                touchStartX = e.changedTouches[0].screenX;
            }, { passive: true });
            viewer.addEventListener('touchend', function(e) {
                var dx = e.changedTouches[0].screenX - touchStartX;
                if (Math.abs(dx) < 50) return;
                var v = window.pinchardViewer;
                if (dx > 0 && v.prevUrl) {
                    window.location.href = v.prevUrl;
                } else if (dx < 0 && v.nextUrl) {
                    window.location.href = v.nextUrl;
                }
            }, { passive: true });
        })();
    </script>
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

$footerScripts = str_replace('PREV_URL', json_encode($prevUrl, $mapJe), $footerScripts);
$footerScripts = str_replace('NEXT_URL', json_encode($nextUrl, $mapJe), $footerScripts);
$footerScripts = str_replace('PRELOAD_URLS', json_encode($preloadUrls, $mapJe), $footerScripts);

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
    $footerScripts .= '            new mapboxgl.Marker().setLngLat([' . json_encode($mapLon, $mapJe) . ', ' . json_encode($mapLat, $mapJe) . "]).addTo(map);\n";
    $footerScripts .= "        });\n    </script>\n";
}

pinchard_layout_footer(['extra_scripts' => $footerScripts]);
