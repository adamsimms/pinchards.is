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
    $maxPhotosPerDay = 1;
    foreach ($photosByDay as $dayGroup) {
        $maxPhotosPerDay = max($maxPhotosPerDay, count($dayGroup['photos']));
    }
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
                            <img class="gallery-photo img-fluid" data-src="<?= pinchard_h($cdnurl . $photo['filename']) ?>" alt="<?= pinchard_h($photo['show_date'] ?? '') ?>" width="288" height="224" decoding="async">
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
pinchard_layout_footer([
    'extra_scripts' => <<<'JS'
    <script>
        (function() {
            var scrollEl = document.getElementById('galleryDaysScroll');
            var layout = document.querySelector('.gallery-days-layout');
            if (!scrollEl || !layout) {
                return;
            }

            var filmstripQuery = window.matchMedia('(min-width: 768px)');
            var feedDateEl = document.getElementById('galleryFeedDate');
            var photos = scrollEl.querySelectorAll('.gallery-photo[data-src]');
            var photoLinks = scrollEl.querySelectorAll('.gallery-day-photo');
            var columns = scrollEl.querySelectorAll('.gallery-day-column');
            var isDragging = false;
            var dragMoved = false;
            var pendingDrag = null;
            var fitRaf = 0;
            var feedDateRaf = 0;
            var activeFeedLabel = feedDateEl ? feedDateEl.textContent : '';

            function isFilmstrip() {
                return filmstripQuery.matches;
            }

            function updateFeedDate() {
                if (!feedDateEl || isFilmstrip() || !columns.length) {
                    return;
                }
                var scrollTop = scrollEl.scrollTop;
                var active = columns[0];
                var showSticky = false;
                for (var i = 0; i < columns.length; i++) {
                    var label = columns[i].querySelector('.gallery-day-label');
                    var labelHeight = label ? label.offsetHeight : 0;
                    // Once this day's inline label has scrolled away, it owns the sticky bar.
                    if (columns[i].offsetTop + labelHeight <= scrollTop + 1) {
                        active = columns[i];
                        showSticky = true;
                    } else {
                        break;
                    }
                }
                feedDateEl.classList.toggle('is-visible', showSticky);
                if (!showSticky) {
                    return;
                }
                var nextLabel = active.getAttribute('data-feed-label') || '';
                if (nextLabel && nextLabel !== activeFeedLabel) {
                    activeFeedLabel = nextLabel;
                    feedDateEl.textContent = nextLabel;
                }
            }

            function scheduleFeedDateUpdate() {
                if (feedDateRaf) return;
                feedDateRaf = requestAnimationFrame(function() {
                    feedDateRaf = 0;
                    updateFeedDate();
                });
            }

            function fitPhotoHeights() {
                if (!isFilmstrip()) {
                    return;
                }
                var styles = getComputedStyle(layout);
                var maxPhotos = parseInt(styles.getPropertyValue('--gallery-days-max-photos'), 10) || 13;
                var colPad = parseFloat(styles.getPropertyValue('--gallery-days-column-pad')) || 12;
                var dateLine = parseFloat(styles.getPropertyValue('--gallery-days-date-line')) || 11;
                var dateBand = dateLine + colPad + 1;
                var available = scrollEl.clientHeight - colPad - dateBand;
                if (available <= 0 || maxPhotos <= 0) {
                    return;
                }
                var photoHeight = Math.floor(available / maxPhotos);
                if (photoHeight < 20) {
                    return;
                }
                var columnWidth = Math.round(photoHeight * 288 / 224);
                var prevHeight = parseFloat(layout.style.getPropertyValue('--gallery-days-photo-height')) || 0;
                if (Math.abs(photoHeight - prevHeight) < 2 && prevHeight > 0) {
                    return;
                }
                layout.style.setProperty('--gallery-days-photo-height', photoHeight + 'px');
                layout.style.setProperty('--gallery-days-column-width', columnWidth + 'px');
            }

            function scheduleFitPhotoHeights() {
                if (!isFilmstrip()) {
                    return;
                }
                if (fitRaf) return;
                fitRaf = requestAnimationFrame(function() {
                    fitRaf = 0;
                    fitPhotoHeights();
                });
            }

            function clearFilmstripSizing() {
                layout.style.removeProperty('--gallery-days-photo-height');
                layout.style.removeProperty('--gallery-days-column-width');
            }

            function onFilmstripModeChange() {
                if (isFilmstrip()) {
                    fitPhotoHeights();
                } else {
                    clearFilmstripSizing();
                    endDrag();
                    updateFeedDate();
                }
            }

            fitPhotoHeights();
            updateFeedDate();
            window.addEventListener('resize', scheduleFitPhotoHeights);
            scrollEl.addEventListener('scroll', scheduleFeedDateUpdate, { passive: true });
            if ('ResizeObserver' in window) {
                new ResizeObserver(scheduleFitPhotoHeights).observe(scrollEl);
            }
            if (document.fonts && document.fonts.ready) {
                document.fonts.ready.then(function() {
                    if (isFilmstrip()) {
                        fitPhotoHeights();
                    }
                });
            }
            if (typeof filmstripQuery.addEventListener === 'function') {
                filmstripQuery.addEventListener('change', onFilmstripModeChange);
            } else if (typeof filmstripQuery.addListener === 'function') {
                filmstripQuery.addListener(onFilmstripModeChange);
            }

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

            if (photos.length && 'IntersectionObserver' in window) {
                var photoObserver = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            loadPhoto(entry.target);
                            photoObserver.unobserve(entry.target);
                        }
                    });
                }, {
                    root: scrollEl,
                    // Prefetch well ahead so 3-up rows are warm before they arrive.
                    rootMargin: isFilmstrip() ? '160px 240px' : '800px 0px'
                });
                photos.forEach(function(img) {
                    photoObserver.observe(img);
                });
            } else {
                photos.forEach(loadPhoto);
            }

            function endDrag() {
                isDragging = false;
                pendingDrag = null;
                scrollEl.classList.remove('is-dragging');
            }

            // Mouse-only drag-to-scroll on the desktop filmstrip.
            scrollEl.addEventListener('pointerdown', function(e) {
                if (!isFilmstrip() || e.pointerType !== 'mouse' || e.button !== 0) return;
                pendingDrag = {
                    startX: e.clientX,
                    scrollLeft: scrollEl.scrollLeft,
                    pointerId: e.pointerId
                };
                dragMoved = false;
                isDragging = false;
            });

            scrollEl.addEventListener('pointermove', function(e) {
                if (!isFilmstrip() || !pendingDrag || e.pointerId !== pendingDrag.pointerId) return;
                var delta = e.clientX - pendingDrag.startX;
                if (!isDragging) {
                    if (Math.abs(delta) <= 4) return;
                    isDragging = true;
                    dragMoved = true;
                    scrollEl.classList.add('is-dragging');
                    if (scrollEl.setPointerCapture) {
                        scrollEl.setPointerCapture(e.pointerId);
                    }
                }
                scrollEl.scrollLeft = pendingDrag.scrollLeft - delta;
            });

            scrollEl.addEventListener('pointerup', function(e) {
                if (!pendingDrag || e.pointerId !== pendingDrag.pointerId) return;
                endDrag();
                window.setTimeout(function() {
                    dragMoved = false;
                }, 0);
            });
            scrollEl.addEventListener('pointercancel', function(e) {
                if (!pendingDrag || e.pointerId !== pendingDrag.pointerId) return;
                endDrag();
                dragMoved = false;
            });

            photoLinks.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    if (dragMoved) {
                        e.preventDefault();
                        return;
                    }
                });
                link.addEventListener('dragstart', function(e) {
                    e.preventDefault();
                });
                link.addEventListener('focus', function() {
                    photoLinks.forEach(function(other) {
                        other.classList.toggle('is-keyboard-focus', other === link);
                    });
                });
            });

            scrollEl.addEventListener('wheel', function(e) {
                if (!isFilmstrip()) return;
                var delta = e.deltaX;
                if (e.shiftKey && e.deltaY !== 0) {
                    delta = e.deltaY;
                } else if (delta === 0 && e.deltaY !== 0) {
                    delta = e.deltaY;
                }
                if (delta === 0) return;
                scrollEl.scrollLeft += delta;
                e.preventDefault();
            }, { passive: false });

            photoLinks.forEach(function(link) {
                link.setAttribute('tabindex', '-1');
            });

            function columnPhotos(col) {
                return Array.prototype.slice.call(col.querySelectorAll('.gallery-day-photo'));
            }

            function focusPhoto(colIndex, photoIndex) {
                if (!columns.length) return;
                colIndex = Math.max(0, Math.min(colIndex, columns.length - 1));
                var col = columns[colIndex];
                var stack = columnPhotos(col);
                if (!stack.length) return;
                photoIndex = Math.max(0, Math.min(photoIndex, stack.length - 1));
                var target = stack[photoIndex];
                photoLinks.forEach(function(link) {
                    link.classList.remove('is-keyboard-focus');
                    link.setAttribute('tabindex', '-1');
                });
                target.classList.add('is-keyboard-focus');
                target.setAttribute('tabindex', '0');
                target.focus({ preventScroll: true });
                if (isFilmstrip()) {
                    var colLeft = col.offsetLeft;
                    var colRight = colLeft + col.offsetWidth;
                    var viewLeft = scrollEl.scrollLeft;
                    var viewRight = viewLeft + scrollEl.clientWidth;
                    if (colLeft < viewLeft) {
                        scrollEl.scrollTo({ left: colLeft, behavior: 'smooth' });
                    } else if (colRight > viewRight) {
                        scrollEl.scrollTo({ left: colRight - scrollEl.clientWidth, behavior: 'smooth' });
                    }
                } else {
                    target.scrollIntoView({ block: 'nearest', inline: 'nearest' });
                }
            }

            function focusedPosition() {
                var active = document.activeElement;
                if (!active || !active.classList.contains('gallery-day-photo')) {
                    return { col: 0, photo: 0 };
                }
                var col = active.closest('.gallery-day-column');
                var colIndex = Array.prototype.indexOf.call(columns, col);
                var stack = columnPhotos(col);
                return { col: colIndex, photo: stack.indexOf(active) };
            }

            scrollEl.addEventListener('keydown', function(e) {
                if (!isFilmstrip()) {
                    return;
                }
                var pos = focusedPosition();
                if (document.activeElement !== scrollEl && !document.activeElement.classList.contains('gallery-day-photo')) {
                    focusPhoto(0, 0);
                    e.preventDefault();
                    return;
                }
                if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    focusPhoto(pos.col - 1, pos.photo);
                } else if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    focusPhoto(pos.col + 1, pos.photo);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    focusPhoto(pos.col, pos.photo - 1);
                } else if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    focusPhoto(pos.col, pos.photo + 1);
                }
            });

            scrollEl.addEventListener('focus', function() {
                if (!isFilmstrip()) {
                    return;
                }
                if (!document.activeElement.classList.contains('gallery-day-photo')) {
                    focusPhoto(0, 0);
                }
            });

            function scrollToDayColumn(target) {
                if (!target) return;
                var dayIndex = Array.prototype.indexOf.call(columns, target);
                requestAnimationFrame(function() {
                    if (isFilmstrip()) {
                        scrollEl.scrollTo({ left: target.offsetLeft, behavior: 'auto' });
                        if (dayIndex >= 0) {
                            focusPhoto(dayIndex, 0);
                        }
                    } else {
                        scrollEl.scrollTop = target.offsetTop;
                        updateFeedDate();
                    }
                });
            }

            var hash = window.location.hash;
            if (hash.indexOf('#day-') === 0) {
                scrollToDayColumn(document.querySelector(hash));
            } else if (hash.indexOf('#month-') === 0) {
                var monthKey = hash.replace('#month-', '');
                scrollToDayColumn(document.querySelector('.gallery-day-column[id^="day-' + monthKey + '"]'));
            }
        })();
    </script>
JS,
]);
