<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/partials/layout.php';

try {
    $cfg = pinchard_config();
    $cdnurl = $cfg['cdn_url_thumbnails'];

    $array = getObjectList($cfg['s3_bucket_thumbnails']);
    usort($array, fn ($a, $b) => $a['date'] <=> $b['date']);
    $photosByMonth = pinchard_group_photos_by_month($array);
    $monthKeys = array_keys($photosByMonth);
} catch (RuntimeException | \Aws\Exception\AwsException $e) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=utf-8');
    if (pinchard_env_non_empty('PINCHARD_DEBUG') === '1') {
        exit($e->getMessage());
    }
    exit('Photo gallery is temporarily unavailable.');
}

pinchard_layout_head("Pinchard's Island — Photo Gallery", [
    'description' => 'Browse the Cloudberry archive — hourly photographs of Pinchard\'s Island, Newfoundland, grouped by month.',
    'body_class' => 'gallery-page',
    'json_ld' => [
        [
            '@type' => 'CollectionPage',
            'name' => "Pinchard's Island — Photo Gallery",
            'description' => 'Browse the Cloudberry archive — hourly photographs of Pinchard\'s Island, Newfoundland, grouped by month.',
            'url' => pinchard_absolute_url('/gallery.php'),
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
    <h1 class="visually-hidden">Pinchard's Island Photo Gallery</h1>
    <div class="gallery-layout">
        <div class="content_area">
            <div class="container-fluid px-0" id="photo_container">
<?php foreach ($photosByMonth as $monthKey => $monthGroup): ?>
                <section class="gallery-month" id="month-<?= pinchard_h($monthKey) ?>" aria-label="<?= pinchard_h($monthGroup['label']) ?>">
                    <div class="row photos g-0">
<?php foreach ($monthGroup['photos'] as $photo): ?>
                        <div class="col-md-5ths col-sm-6 col-12 photoElement">
                            <a href="index.php?filename=<?= pinchard_h($photo['filename']) ?>" class="photoBox">
                                <img class="gallery-photo img-fluid" data-src="<?= pinchard_h($cdnurl . $photo['filename']) ?>" alt="<?= pinchard_h($photo['show_date'] ?? '') ?>" width="288" height="224">
                                <div class="photo-box-caption">
                                    <div class="photo-box-caption-content"><?= pinchard_h($photo['show_date'] ?? '') ?></div>
                                </div>
                            </a>
                        </div>
<?php endforeach; ?>
                    </div>
                </section>
<?php endforeach; ?>
            </div>
        </div>

        <footer class="gallery-header">
            <nav class="gallery-timeline" aria-label="Gallery timeline">
                <div class="gallery-timeline-scroll">
                    <div class="gallery-timeline-track">
<?php $isFirstMonth = true; foreach ($photosByMonth as $monthKey => $monthGroup): ?>
                        <a href="#month-<?= pinchard_h($monthKey) ?>" class="gallery-timeline-marker<?= $isFirstMonth ? ' is-active' : '' ?>" data-month="<?= pinchard_h($monthKey) ?>" data-label="<?= pinchard_h($monthGroup['label']) ?>" title="<?= pinchard_h($monthGroup['label']) ?>">
                            <span class="gallery-timeline-dot" aria-hidden="true"></span>
                            <span class="gallery-timeline-label"><?= pinchard_h(pinchard_month_timeline_label($monthKey)) ?></span>
                        </a>
<?php $isFirstMonth = false; endforeach; ?>
<?php if ($monthKeys !== []): ?>
                        <a href="#month-<?= pinchard_h($monthKeys[count($monthKeys) - 1]) ?>" class="gallery-timeline-marker gallery-timeline-latest" data-label="<?= pinchard_h($photosByMonth[$monthKeys[count($monthKeys) - 1]]['label']) ?>" title="Latest photographs">
                            <span class="gallery-timeline-dot" aria-hidden="true"></span>
                            <span class="gallery-timeline-label">Latest</span>
                        </a>
<?php endif; ?>
                    </div>
                </div>
            </nav>
        </footer>
    </div>

<?php
pinchard_layout_footer([
    'extra_scripts' => <<<'JS'
    <script>
        (function() {
            var photos = document.querySelectorAll('.gallery-photo[data-src]');

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

            if (photos.length) {
                if ('IntersectionObserver' in window) {
                    var photoObserver = new IntersectionObserver(function(entries) {
                        entries.forEach(function(entry) {
                            if (entry.isIntersecting) {
                                loadPhoto(entry.target);
                                photoObserver.unobserve(entry.target);
                            }
                        });
                    }, { rootMargin: '240px 0px' });
                    photos.forEach(function(img) {
                        photoObserver.observe(img);
                    });
                } else {
                    photos.forEach(loadPhoto);
                }
            }

            var markers = document.querySelectorAll('.gallery-timeline-marker');
            var sections = document.querySelectorAll('.gallery-month');
            if (!markers.length || !sections.length || !('IntersectionObserver' in window)) {
                return;
            }

            var activeMonth = null;

            function scrollMarkerIntoView(marker) {
                if (!marker || !marker.parentElement) return;
                var scroll = marker.closest('.gallery-timeline-scroll');
                if (!scroll) return;
                var markerLeft = marker.offsetLeft;
                var markerWidth = marker.offsetWidth;
                var scrollWidth = scroll.clientWidth;
                var target = markerLeft - (scrollWidth / 2) + (markerWidth / 2);
                scroll.scrollTo({ left: Math.max(0, target), behavior: 'smooth' });
            }

            function setActive(monthKey) {
                if (activeMonth === monthKey) return;
                activeMonth = monthKey;
                var activeMarker = null;
                markers.forEach(function(marker) {
                    var match = marker.classList.contains('gallery-timeline-latest')
                        ? monthKey === sections[sections.length - 1].id.replace('month-', '')
                        : marker.getAttribute('data-month') === monthKey;
                    marker.classList.toggle('is-active', match);
                    if (match) {
                        activeMarker = marker;
                    }
                });
                scrollMarkerIntoView(activeMarker);
            }

            var monthObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        setActive(entry.target.id.replace('month-', ''));
                    }
                });
            }, {
                root: null,
                rootMargin: '-40% 0px -55% 0px',
                threshold: 0
            });

            sections.forEach(function(section) {
                monthObserver.observe(section);
            });
        })();
    </script>
JS,
]);
