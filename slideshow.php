<?php

declare(strict_types=1);

/**
 * Query params:
 *   display=SECONDS  hold time per image
 *   fade=SECONDS     crossfade duration (default 8)
 *   kiosk=1          start in image-only mode (toggle with F / Escape)
 */
$display = 0.0;
$fade = 8.0;
if (isset($_GET['display']) && $_GET['display'] !== '') {
    $display = max(0.1, min(600.0, (float) $_GET['display']));
}
if (isset($_GET['fade']) && $_GET['fade'] !== '') {
    $fade = max(0.0, min(60.0, (float) $_GET['fade']));
}
$kiosk = isset($_GET['kiosk']) && $_GET['kiosk'] !== '' && $_GET['kiosk'] !== '0';

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/partials/layout.php';

try {
    $cfg = pinchard_config();
    $cdnurl = $cfg['cdn_url_full'];
    $array = getObjectList($cfg['s3_bucket_full']);
    usort($array, fn ($a, $b) => $a['date'] <=> $b['date']);
    $cloudberryArchiveSpan = pinchard_cloudberry_archive_span($array);
    $slideshowDescription = pinchard_cloudberry_slideshow_description($cloudberryArchiveSpan);
    $slideshowTimeline = pinchard_slideshow_timeline($array);
} catch (RuntimeException | \Aws\Exception\AwsException $e) {
    if (pinchard_env_non_empty('PINCHARD_DEBUG') === '1') {
        http_response_code(503);
        header('Content-Type: text/plain; charset=utf-8');
        exit($e->getMessage());
    }
    pinchard_unavailable_page('Slideshow is temporarily unavailable.');
}

$bodyClass = 'slideshow-page' . ($kiosk ? ' slideshow-page--kiosk' : '');

pinchard_layout_head('Cloudberry — Slideshow', [
    'description' => $slideshowDescription,
    'body_class' => $bodyClass,
    'json_ld' => [
        [
            '@type' => 'WebPage',
            'name' => 'Cloudberry — Slideshow',
            'description' => $slideshowDescription,
            'url' => pinchard_absolute_url('/slideshow.php'),
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => 'Cloudberry',
                'url' => pinchard_absolute_url('/index.php'),
            ],
        ],
    ],
]);

pinchard_layout_nav(['active' => 'slideshow']);
$slideshowFadeStyle = '--slideshow-fade: ' . pinchard_h((string) $fade) . 's;';
?>
    <h1 class="visually-hidden">Cloudberry Slideshow</h1>
    <div class="slideshow-shell">
        <div class="slideshow-viewport" id="slideshow" style="<?= $slideshowFadeStyle ?>" aria-live="polite" aria-label="Photograph slideshow"></div>
<?php if ($slideshowTimeline !== null): ?>
<?php
    $timelineCount = count($slideshowTimeline['entries']);
    $timelinePosition = $slideshowTimeline['index'] + 1;
    $timelineDate = $slideshowTimeline['entries'][$slideshowTimeline['index']]['d'];
    $timelineAria = pinchard_h('Photograph ' . $timelinePosition . ' of ' . $timelineCount . ', ' . $timelineDate);
?>
        <div class="slideshow-bar">
            <nav class="viewer-timeline" id="viewerTimeline" aria-label="Photograph timeline">
                <span class="viewer-timeline-date" id="viewerTimelinePosition" aria-live="polite"><?= pinchard_h($timelineDate) ?></span>
                <div class="viewer-timeline-track">
                    <input
                        type="range"
                        class="viewer-timeline-range"
                        id="viewerTimelineRange"
                        min="0"
                        max="<?= $timelineCount - 1 ?>"
                        value="<?= $slideshowTimeline['index'] ?>"
                        step="1"
                        aria-label="<?= $timelineAria ?>"
                        aria-valuemin="1"
                        aria-valuemax="<?= $timelineCount ?>"
                        aria-valuenow="<?= $timelinePosition ?>"
                        aria-valuetext="<?= $timelineAria ?>"
                    >
                </div>
            </nav>
        </div>
<?php endif; ?>
    </div>

<?php
$footerScripts = pinchard_slideshow_scripts([
    'display' => $display,
    'fade' => $fade,
    'images' => $array,
    'timeline' => $slideshowTimeline,
    'cdnurl' => $cdnurl,
    'scope' => 'root',
]);

pinchard_layout_footer(['extra_scripts' => $footerScripts]);
