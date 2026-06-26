<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/partials/layout.php';

try {
    $cfg = pinchard_config();
    $cdnurl = $cfg['cdn_url_thumbnails'];

    $array = getObjectList($cfg['s3_bucket_thumbnails']);
    usort($array, fn ($a, $b) => $a['date'] <=> $b['date']);
    $photosByDay = pinchard_group_photos_by_day($array);
} catch (RuntimeException | \Aws\Exception\AwsException $e) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=utf-8');
    if (pinchard_env_non_empty('PINCHARD_DEBUG') === '1') {
        exit($e->getMessage());
    }
    exit('Photo gallery is temporarily unavailable.');
}

pinchard_layout_head("Pinchard's Island — Day Gallery", [
    'description' => 'Browse the Cloudberry archive day by day — one column per day, photographs stacked from morning to evening.',
    'body_class' => 'gallery-days-page',
    'json_ld' => [
        [
            '@type' => 'CollectionPage',
            'name' => "Pinchard's Island — Day Gallery",
            'description' => 'Browse the Cloudberry archive day by day — one column per day, photographs stacked from morning to evening.',
            'url' => pinchard_absolute_url('/gallery-days.php'),
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => "Pinchard's Island — Cloudberry",
                'url' => pinchard_absolute_url('/index.php'),
            ],
        ],
    ],
]);

pinchard_layout_nav(['active' => 'gallery']);
?>
    <h1 class="visually-hidden">Pinchard's Island Day Gallery</h1>
    <div class="gallery-days-layout">
        <div class="gallery-days-scroll" id="galleryDaysScroll">
            <div class="gallery-days-track" id="galleryDaysTrack">
<?php
    $prevMonthKey = null;
    foreach ($photosByDay as $dayKey => $dayGroup):
        $isMonthStart = $prevMonthKey !== $dayGroup['month_key'];
        $prevMonthKey = $dayGroup['month_key'];
?>
                <section class="gallery-day-column<?= $isMonthStart ? ' is-month-start' : '' ?>" id="day-<?= pinchard_h($dayKey) ?>" aria-label="<?= pinchard_h($dayGroup['long_label']) ?>">
                    <header class="gallery-day-header">
<?php if ($isMonthStart): ?>
                        <div class="gallery-day-month-marker" aria-hidden="true"><?= pinchard_h(pinchard_month_timeline_label($dayGroup['month_key'])) ?></div>
<?php endif; ?>
                        <div class="gallery-day-label" title="<?= pinchard_h($dayGroup['long_label']) ?>"><?= pinchard_h($dayGroup['label']) ?></div>
                    </header>
                    <div class="gallery-day-stack">
<?php foreach ($dayGroup['photos'] as $photo): ?>
                        <a href="index.php?filename=<?= pinchard_h($photo['filename']) ?>" class="gallery-day-photo photoBox">
                            <img class="gallery-photo img-fluid" data-src="<?= pinchard_h($cdnurl . $photo['filename']) ?>" alt="<?= pinchard_h($photo['show_date'] ?? '') ?>" width="288" height="224">
                            <div class="photo-box-caption">
                                <div class="photo-box-caption-content"><?= pinchard_h($photo['show_date'] ?? '') ?></div>
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
pinchard_layout_footer([
    'extra_scripts' => <<<'JS'
    <script>
        (function() {
            var photos = document.querySelectorAll('.gallery-days-page .gallery-photo[data-src]');

            function markLoaded(img) {
                var box = img.closest('.photoBox');
                if (box) {
                    box.classList.add('is-loaded');
                }
            }

            function loadPhoto(img) {
                if (img.dataset.loading) return;
                img.dataset.loading = '1';
                var src = img.getAttribute('data-src');
                if (!src) return;

                function done() {
                    markLoaded(img);
                    img.removeAttribute('data-src');
                }

                img.addEventListener('load', done, { once: true });
                img.addEventListener('error', done, { once: true });
                img.src = src;
                if (img.complete) {
                    done();
                }
            }

            if (!photos.length) {
                return;
            }

            if ('IntersectionObserver' in window) {
                var photoObserver = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            loadPhoto(entry.target);
                            photoObserver.unobserve(entry.target);
                        }
                    });
                }, { root: document.getElementById('galleryDaysScroll'), rootMargin: '320px 480px' });
                photos.forEach(function(img) {
                    photoObserver.observe(img);
                });
            } else {
                photos.forEach(loadPhoto);
            }

            var scrollEl = document.getElementById('galleryDaysScroll');
            var hash = window.location.hash;
            if (scrollEl && hash && hash.indexOf('#day-') === 0) {
                var target = document.querySelector(hash);
                if (target) {
                    requestAnimationFrame(function() {
                        var left = target.offsetLeft - 16;
                        scrollEl.scrollTo({ left: Math.max(0, left), behavior: 'auto' });
                    });
                }
            }
        })();
    </script>
JS,
]);
