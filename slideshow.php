<?php

declare(strict_types=1);

$display = 5.0;
$fade = 1.0;
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
} catch (RuntimeException | \Aws\Exception\AwsException $e) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=utf-8');
    if (pinchard_env_non_empty('PINCHARD_DEBUG') === '1') {
        exit($e->getMessage());
    }
    exit('Slideshow is temporarily unavailable.');
}

$mapJe = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;

pinchard_layout_head("Pinchard's Island — Slideshow", [
    'description' => 'Watch Cloudberry photographs from Pinchard\'s Island in sequence — an hourly documentary of the view from Precious Memories cabin.',
    'body_class' => 'slideshow-page',
    'json_ld' => [
        [
            '@type' => 'WebPage',
            'name' => "Pinchard's Island — Slideshow",
            'description' => 'Watch Cloudberry photographs from Pinchard\'s Island in sequence — an hourly documentary of the view from Precious Memories cabin.',
            'url' => pinchard_absolute_url('/slideshow.php'),
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => "Pinchard's Island — Cloudberry",
                'url' => pinchard_absolute_url('/index.php'),
            ],
        ],
    ],
]);

pinchard_layout_nav(['active' => 'slideshow']);
?>
    <h1 class="visually-hidden">Pinchard's Island Slideshow</h1>
    <div class="slideshow-viewport" id="slideshow" aria-live="polite" aria-label="Photograph slideshow"></div>

<?php
$footerScripts = '<script>' . "\n";
$footerScripts .= 'var pinchardSlideshow = {' . "\n";
$footerScripts .= '  display: ' . json_encode($display, $mapJe) . ' * 1000,' . "\n";
$footerScripts .= '  fade: ' . json_encode($fade, $mapJe) . ' * 1000,' . "\n";
$footerScripts .= '  images: ' . json_encode($array, $mapJe) . ',' . "\n";
$footerScripts .= '  cdnurl: ' . json_encode($cdnurl, $mapJe) . "\n";
$footerScripts .= '};' . "\n";
$footerScripts .= <<<'JS'
(function($) {
    var cfg = window.pinchardSlideshow;
    var container = document.getElementById('slideshow');
    var navDate = document.getElementById('navSlideshowDate');
    if (!cfg.images.length) {
        container.textContent = 'No photographs available.';
        return;
    }

    var index = 0;
    var currentImg = null;
    var advanceTimer = null;
    var paused = false;
    var navToggle = document.getElementById('navSlideshowToggle');

    function clearAdvanceTimer() {
        if (advanceTimer !== null) {
            clearTimeout(advanceTimer);
            advanceTimer = null;
        }
    }

    function scheduleAdvance() {
        clearAdvanceTimer();
        if (paused) {
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
        if (!paused) {
            scheduleAdvance();
        }
    }

    function photoUrl(i) {
        return cfg.cdnurl + cfg.images[i].filename;
    }

    function updateNavDate() {
        var item = cfg.images[index];
        if (navDate && item) {
            navDate.textContent = item.show_date || '';
        }
    }

    function preloadIndex(i) {
        if (i < 0 || i >= cfg.images.length) return;
        var img = new Image();
        img.src = photoUrl(i);
    }

    function showIndex(i) {
        index = i;
        var img = document.createElement('img');
        img.className = 'slideshow-photo';
        img.src = photoUrl(i);
        img.alt = cfg.images[i].show_date || '';
        img.style.display = 'none';
        container.appendChild(img);

        preloadIndex((i + 1) % cfg.images.length);

        if (!currentImg) {
            img.style.display = 'block';
            currentImg = img;
            updateNavDate();
            scheduleAdvance();
            return;
        }

        $(img).fadeIn(cfg.fade, 'linear', function() {
            var old = currentImg;
            currentImg = img;
            if (old && old.parentNode) {
                old.parentNode.removeChild(old);
            }
            updateNavDate();
            scheduleAdvance();
        });
    }

    function advance() {
        if (paused) {
            return;
        }
        showIndex((index + 1) % cfg.images.length);
    }

    showIndex(0);

    if (navToggle) {
        navToggle.addEventListener('click', function() {
            setPaused(!paused);
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            window.location.href = 'gallery.php';
        }
    });
})(jQuery);
JS;
$footerScripts .= '</script>';

pinchard_layout_footer(['extra_scripts' => $footerScripts]);
