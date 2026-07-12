<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/partials/layout.php';

try {
    $cfg = pinchard_config();
    $cdnurl = $cfg['cdn_url_thumbnails'];

    $array = getObjectList($cfg['s3_bucket_thumbnails']);
    usort($array, fn ($a, $b) => pinchard_photo_sort_key($a) <=> pinchard_photo_sort_key($b));
    $photosByDay = pinchard_group_photos_by_day($array);
    // Desktop filmstrip sizes rows to fit this many photos in the viewport.
    // Cap so busy days (e.g. test bursts) don't shrink thumbs to a sliver.
    $galleryMaxPhotosPerDay = 12;
    $maxPhotosPerDay = 1;
    foreach ($photosByDay as $dayGroup) {
        $maxPhotosPerDay = max($maxPhotosPerDay, count($dayGroup['photos']));
    }
    $maxPhotosPerDay = min($galleryMaxPhotosPerDay, $maxPhotosPerDay);
    $cloudberryArchiveSpan = pinchard_cloudberry_archive_span($array);
    $galleryDescription = pinchard_cloudberry_gallery_description($cloudberryArchiveSpan);
} catch (RuntimeException | \Aws\Exception\AwsException $e) {
    if (pinchard_env_non_empty('PINCHARD_DEBUG') === '1') {
        http_response_code(503);
        header('Content-Type: text/plain; charset=utf-8');
        exit($e->getMessage());
    }
    pinchard_unavailable_page('Photo gallery is temporarily unavailable.');
}

pinchard_layout_head('Cloudberry — Photo Gallery', [
    'description' => $galleryDescription,
    'body_class' => 'gallery-page',
    'json_ld' => [
        [
            '@type' => 'CollectionPage',
            'name' => 'Cloudberry — Photo Gallery',
            'description' => $galleryDescription,
            'url' => pinchard_absolute_url('/gallery.php'),
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => 'Cloudberry',
                'url' => pinchard_absolute_url('/index.php'),
            ],
        ],
    ],
]);

pinchard_layout_nav(['active' => 'gallery']);
?>
    <h1 class="visually-hidden">Cloudberry Photo Gallery</h1>
    <div class="gallery-days-layout" style="--gallery-days-max-photos: <?= (int) $maxPhotosPerDay ?>">
<?php
    $initialFeedLabel = '';
    foreach ($photosByDay as $dayGroup) {
        $initialFeedLabel = $dayGroup['feed_label'] ?? '';
        break;
    }
?>
        <div class="gallery-feed-date" id="galleryFeedDate" aria-live="polite"><?= pinchard_h($initialFeedLabel) ?></div>
        <div class="gallery-days-scroll" id="galleryDaysScroll" tabindex="0" aria-label="Photo gallery. On phones, scroll vertically by day. On larger screens, drag or scroll horizontally across days. Arrow keys move between photographs.">
            <div class="gallery-days-track" id="galleryDaysTrack">
<?php foreach ($photosByDay as $dayKey => $dayGroup): ?>
                <section class="gallery-day-column" id="day-<?= pinchard_h($dayKey) ?>" aria-label="<?= pinchard_h($dayGroup['long_label']) ?>" data-feed-label="<?= pinchard_h($dayGroup['feed_label']) ?>">
                    <div class="gallery-day-label" title="<?= pinchard_h($dayGroup['long_label']) ?>">
                        <span class="gallery-day-label-compact"><?= pinchard_h($dayGroup['label']) ?></span>
                        <span class="gallery-day-label-feed"><?= pinchard_h($dayGroup['feed_label']) ?></span>
                    </div>
                    <div class="gallery-day-stack">
<?php foreach ($dayGroup['photos'] as $photo): ?>
                        <a href="index.php?filename=<?= pinchard_h(rawurlencode($photo['filename'])) ?>" class="gallery-day-photo photoBox">
                            <img class="gallery-photo img-fluid" data-src="<?= pinchard_h($cdnurl . $photo['filename']) ?>" alt="<?= pinchard_h(pinchard_photo_alt_text($photo['date'])) ?>" width="288" height="224" decoding="async">
                            <div class="photo-box-caption">
                                <div class="photo-box-caption-content"><?= pinchard_h(pinchard_show_time($photo['capture_date'] ?? $photo['date'])) ?></div>
                            </div>
                        </a>
<?php endforeach; ?>
                    </div>
                </section>
<?php endforeach; ?>
            </div>
        </div>
    </div>

<?php
$galleryJs = pinchard_h(pinchard_page_asset_url('js/gallery.js'));
pinchard_layout_footer([
	'extra_scripts' => '    <script src="' . $galleryJs . '"></script>' . "\n",
]);
