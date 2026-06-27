<?php

declare(strict_types=1);

$display = 0.0;
$fade = 8.0;
if (isset($_GET['display']) && $_GET['display'] !== '') {
    $display = max(0.1, min(600.0, (float) $_GET['display']));
}
if (isset($_GET['fade']) && $_GET['fade'] !== '') {
    $fade = max(0.0, min(60.0, (float) $_GET['fade']));
}

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
    http_response_code(503);
    header('Content-Type: text/plain; charset=utf-8');
    if (pinchard_env_non_empty('PINCHARD_DEBUG') === '1') {
        exit($e->getMessage());
    }
    exit('Slideshow is temporarily unavailable.');
}

$mapJe = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;

pinchard_layout_head('Cloudberry — Slideshow', [
    'description' => $slideshowDescription,
    'body_class' => 'slideshow-page',
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
$footerScripts = '<script>' . "\n";
$footerScripts .= 'var pinchardSlideshow = {' . "\n";
$footerScripts .= '  display: ' . json_encode($display, $mapJe) . ' * 1000,' . "\n";
$footerScripts .= '  fade: ' . json_encode($fade, $mapJe) . ' * 1000,' . "\n";
$footerScripts .= '  images: ' . json_encode($array, $mapJe) . ',' . "\n";
$footerScripts .= '  timeline: ' . json_encode($slideshowTimeline, $mapJe) . ',' . "\n";
$footerScripts .= '  cdnurl: ' . json_encode($cdnurl, $mapJe) . "\n";
$footerScripts .= '};' . "\n";
$footerScripts .= <<<'JS'
(function() {
    var cfg = window.pinchardSlideshow;
    var container = document.getElementById('slideshow');
    var timelineRange = document.getElementById('viewerTimelineRange');
    var timelinePosition = document.getElementById('viewerTimelinePosition');
    if (!cfg.images.length) {
        container.textContent = 'No photographs available.';
        return;
    }

    var index = 0;
    var currentImg = null;
    var fadingOutImg = null;
    var advanceTimer = null;
    var paused = false;
    var scrubbing = false;
    var crossfading = false;
    var navToggle = document.getElementById('navSlideshowToggle');
    var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var fadeMs = reducedMotion ? 0 : cfg.fade;

    function clearAdvanceTimer() {
        if (advanceTimer !== null) {
            clearTimeout(advanceTimer);
            advanceTimer = null;
        }
    }

    function scheduleAdvance() {
        clearAdvanceTimer();
        if (paused || scrubbing || crossfading) {
            return;
        }
        advanceTimer = setTimeout(advance, cfg.display);
    }

    function setPaused(nextPaused) {
        paused = nextPaused;
        clearAdvanceTimer();
        if (navToggle) {
            navToggle.classList.toggle('is-paused', paused);
            navToggle.setAttribute('aria-label', paused ? 'Resume slideshow' : 'Pause slideshow');
        }
        if (!paused && !scrubbing && !crossfading) {
            scheduleAdvance();
        }
    }

    function photoUrl(i) {
        return cfg.cdnurl + cfg.images[i].filename;
    }

    function timelineEntry(idx) {
        if (!cfg.timeline || !cfg.timeline.entries || !cfg.timeline.entries.length) {
            return null;
        }
        return cfg.timeline.entries[Math.max(0, Math.min(cfg.timeline.entries.length - 1, idx))];
    }

    function updateTimelineUi(idx) {
        if (!timelineRange || !cfg.timeline || !cfg.timeline.entries) {
            return;
        }
        var entry = timelineEntry(idx);
        if (!entry) {
            return;
        }
        var count = cfg.timeline.entries.length;
        var position = idx + 1;
        timelineRange.value = String(idx);
        timelineRange.setAttribute('aria-valuenow', String(position));
        timelineRange.setAttribute('aria-valuetext', 'Photograph ' + position + ' of ' + count + ', ' + entry.d);
        if (timelinePosition) {
            timelinePosition.textContent = entry.d;
        }
    }

    function preloadIndex(i) {
        if (i < 0 || i >= cfg.images.length) {
            return;
        }
        var img = new Image();
        img.src = photoUrl(i);
    }

    function removeImage(img) {
        if (img && img.parentNode) {
            img.parentNode.removeChild(img);
        }
    }

    function finishCrossfade(nextImg) {
        removeImage(fadingOutImg);
        fadingOutImg = null;
        currentImg = nextImg;
        crossfading = false;
        scheduleAdvance();
    }

    function showIndex(i, options) {
        options = options || {};
        var force = !!options.force;
        if (!force && i === index && currentImg) {
            return;
        }

        index = i;
        updateTimelineUi(i);

        var img = document.createElement('img');
        img.className = 'slideshow-photo';
        img.src = photoUrl(i);
        img.alt = cfg.images[i].show_date || '';
        container.appendChild(img);

        preloadIndex((i + 1) % cfg.images.length);

        if (!currentImg) {
            img.style.transition = 'none';
            img.classList.add('is-visible');
            requestAnimationFrame(function() {
                img.style.transition = '';
            });
            currentImg = img;
            scheduleAdvance();
            return;
        }

        crossfading = true;
        clearAdvanceTimer();
        fadingOutImg = currentImg;
        fadingOutImg.classList.add('is-fading-out');
        fadingOutImg.classList.remove('is-visible');

        if (fadeMs === 0) {
            removeImage(fadingOutImg);
            fadingOutImg = null;
            img.classList.add('is-visible');
            currentImg = img;
            crossfading = false;
            scheduleAdvance();
            return;
        }

        requestAnimationFrame(function() {
            requestAnimationFrame(function() {
                img.classList.add('is-visible');
            });
        });

        var settled = false;
        function onFadeComplete(e) {
            if (settled || e.target !== img || e.propertyName !== 'opacity') {
                return;
            }
            settled = true;
            img.removeEventListener('transitionend', onFadeComplete);
            finishCrossfade(img);
        }

        img.addEventListener('transitionend', onFadeComplete);
        window.setTimeout(function() {
            if (!settled) {
                settled = true;
                img.removeEventListener('transitionend', onFadeComplete);
                finishCrossfade(img);
            }
        }, fadeMs + 120);
    }

    function advance() {
        if (paused || scrubbing || crossfading) {
            return;
        }
        showIndex((index + 1) % cfg.images.length);
    }

    showIndex(0, { force: true });

    if (navToggle) {
        navToggle.addEventListener('click', function() {
            setPaused(!paused);
        });
    }

    if (timelineRange && cfg.timeline && cfg.timeline.entries && cfg.timeline.entries.length > 1) {
        timelineRange.addEventListener('input', function() {
            updateTimelineUi(parseInt(timelineRange.value, 10));
        });

        timelineRange.addEventListener('change', function() {
            var nextIndex = parseInt(timelineRange.value, 10);
            showIndex(nextIndex, { force: true });
            if (!paused) {
                scheduleAdvance();
            }
        });

        timelineRange.addEventListener('pointerdown', function() {
            scrubbing = true;
            clearAdvanceTimer();
        });

        timelineRange.addEventListener('pointerup', function() {
            scrubbing = false;
            if (!paused && !crossfading) {
                scheduleAdvance();
            }
        });

        timelineRange.addEventListener('pointercancel', function() {
            scrubbing = false;
            if (!paused && !crossfading) {
                scheduleAdvance();
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (window.pinchardNavigate) {
                window.pinchardNavigate('gallery.php');
            } else {
                window.location.href = 'gallery.php';
            }
        }
    });
})();
JS;
$footerScripts .= '</script>';

pinchard_layout_footer(['extra_scripts' => $footerScripts]);
