(function() {
    'use strict';

    var cfg = window.pinchardViewer;
    if (!cfg || !cfg.cdnUrl || !Array.isArray(cfg.filenames) || cfg.filenames.length === 0) {
        return;
    }

    var motion = window.pinchardMotion;
    var FADE_MS = cfg.fadeMs != null ? cfg.fadeMs : 1000;
    var INTRO_FADE_MS = cfg.introFadeMs != null ? cfg.introFadeMs : 700;
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        FADE_MS = 0;
        INTRO_FADE_MS = 0;
    }
    var placeholder = document.getElementById('preview_image');
    var viewer = document.getElementById('photoViewer');
    var drawer = document.getElementById('detailDrawer');
    var toggle = document.getElementById('detailToggle');
    var prevLink = document.querySelector('.viewer-photo-prev');
    var nextLink = document.querySelector('.viewer-photo-next');
    var timelineRange = document.getElementById('viewerTimelineRange');
    var timelinePosition = document.getElementById('viewerTimelinePosition');
    var titleEl = document.getElementById('viewerPhotoTitle');
    var dateEl = document.getElementById('viewerPhotoDate');
    var cameraEl = document.getElementById('viewerCameraLines');
    var gpsEl = document.getElementById('viewerGpsLines');
    var weatherArea = document.getElementById('viewerWeatherArea');
    var weatherEl = document.getElementById('viewerWeatherLines');
    var citationBtn = document.querySelector('.detail-citation-copy');
    var hiddenTitle = document.querySelector('.viewer-page h1.visually-hidden');

    if (!placeholder || !viewer) {
        return;
    }

    var currentMainImg = null;
    var transitioning = false;
    var navDirection = 1;
    var metadataRequest = null;
    var prefetchCache = Object.create(null);

    cfg.currentIndex = typeof cfg.currentIndex === 'number' ? cfg.currentIndex : 0;
    cfg.currentFilename = cfg.filenames[cfg.currentIndex] || cfg.currentFilename || '';

    function photoPageUrl(filename) {
        return 'index.php?filename=' + encodeURIComponent(filename);
    }

    function imageUrl(filename) {
        return cfg.cdnUrl + filename;
    }

    function setPlaceholderUnderlay(url) {
        if (!url) {
            return;
        }
        placeholder.style.backgroundImage = 'url("' + url.replace(/"/g, '\\"') + '")';
        placeholder.style.backgroundSize = 'cover';
        placeholder.style.backgroundPosition = 'center';
    }

    function filenameFromIndex(index) {
        return cfg.filenames[index] || null;
    }

    function indexFromFilename(filename) {
        return cfg.filenames.indexOf(filename);
    }

    function preloadUrl(url) {
        if (prefetchCache[url]) {
            return prefetchCache[url];
        }
        prefetchCache[url] = new Promise(function(resolve, reject) {
            var img = new Image();
            img.onload = function() { resolve(img); };
            img.onerror = reject;
            img.src = url;
        });
        return prefetchCache[url];
    }

    function prefetchAdjacent() {
        var prev = filenameFromIndex(cfg.currentIndex - 1);
        var next = filenameFromIndex(cfg.currentIndex + 1);
        if (prev) {
            preloadUrl(imageUrl(prev));
        }
        if (next) {
            preloadUrl(imageUrl(next));
        }
    }

    function setNavLinkState(link, filename) {
        if (!link) {
            return;
        }
        if (!filename) {
            link.classList.add('is-hidden');
            link.setAttribute('aria-hidden', 'true');
            link.setAttribute('tabindex', '-1');
            link.setAttribute('href', photoPageUrl(''));
            return;
        }
        link.classList.remove('is-hidden');
        link.removeAttribute('aria-hidden');
        link.removeAttribute('tabindex');
        link.setAttribute('href', photoPageUrl(filename));
    }

    function updateNavLinks() {
        var prev = filenameFromIndex(cfg.currentIndex - 1);
        var next = filenameFromIndex(cfg.currentIndex + 1);
        cfg.prevUrl = prev ? photoPageUrl(prev) : '';
        cfg.nextUrl = next ? photoPageUrl(next) : '';
        setNavLinkState(prevLink, prev);
        setNavLinkState(nextLink, next);
        prefetchAdjacent();
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
        var position = idx + 1;
        var count = cfg.timeline.entries.length;
        timelineRange.value = String(idx);
        timelineRange.setAttribute('aria-valuenow', String(position));
        timelineRange.setAttribute('aria-valuetext', 'Photograph ' + position + ' of ' + count + ', ' + entry.d);
        if (timelinePosition) {
            if (motion && typeof motion.fadeText === 'function') {
                motion.fadeText(timelinePosition, entry.d);
            } else {
                timelinePosition.textContent = entry.d;
            }
        }
    }

    function syncTimelineToFilename(filename) {
        if (!cfg.timeline || !cfg.timeline.entries) {
            return;
        }
        for (var i = 0; i < cfg.timeline.entries.length; i++) {
            if (cfg.timeline.entries[i].f === filename) {
                updateTimelineUi(i);
                return;
            }
        }
    }

    function updateDocumentTitle(title) {
        document.title = 'Cloudberry — ' + title;
        if (hiddenTitle) {
            hiddenTitle.textContent = 'Cloudberry — ' + title;
        }
    }

    function updateMap(payload) {
        var mapState = window.pinchardPhotoMap;
        if (!mapState || !mapState.map || !mapState.marker) {
            return;
        }
        mapState.marker.setLngLat([payload.mapLon, payload.mapLat]);
        mapState.map.easeTo({
            center: [payload.mapLon, payload.mapLat],
            duration: FADE_MS
        });
    }

    function applyMetadata(payload) {
        if (payload.filename !== cfg.currentFilename) {
            return;
        }
        var canFade = motion && motion.available;
        if (titleEl) {
            if (canFade && typeof motion.fadeText === 'function') {
                motion.fadeText(titleEl, payload.photoTitle);
            } else {
                titleEl.textContent = payload.photoTitle;
            }
        }
        if (dateEl) {
            if (canFade && typeof motion.fadeHtml === 'function') {
                motion.fadeHtml(dateEl, payload.convertedDate);
            } else {
                dateEl.innerHTML = payload.convertedDate;
            }
        }
        if (cameraEl) {
            if (canFade && typeof motion.fadeHtml === 'function') {
                motion.fadeHtml(cameraEl, payload.cameraLinesHtml);
            } else {
                cameraEl.innerHTML = payload.cameraLinesHtml;
            }
        }
        if (gpsEl) {
            if (canFade && typeof motion.fadeHtml === 'function') {
                motion.fadeHtml(gpsEl, payload.gpsHtml);
            } else {
                gpsEl.innerHTML = payload.gpsHtml;
            }
        }
        if (weatherEl && weatherArea) {
            var weatherHtml = typeof payload.weatherHtml === 'string' ? payload.weatherHtml : '';
            if (weatherHtml === '') {
                weatherArea.classList.add('is-hidden');
                weatherEl.innerHTML = '';
            } else {
                weatherArea.classList.remove('is-hidden');
                if (canFade && typeof motion.fadeHtml === 'function') {
                    motion.fadeHtml(weatherEl, weatherHtml);
                } else {
                    weatherEl.innerHTML = weatherHtml;
                }
            }
        }
        if (citationBtn) {
            citationBtn.setAttribute('data-citation', payload.citation);
        }
        updateDocumentTitle(payload.photoTitle);
        updateMap(payload);
        if (typeof payload.timelineIndex === 'number') {
            updateTimelineUi(payload.timelineIndex);
            if (payload.showDate && cfg.timeline && cfg.timeline.entries && cfg.timeline.entries[payload.timelineIndex]) {
                cfg.timeline.entries[payload.timelineIndex].d = payload.showDate;
                if (timelinePosition) {
                    if (canFade && typeof motion.fadeText === 'function') {
                        motion.fadeText(timelinePosition, payload.showDate);
                    } else {
                        timelinePosition.textContent = payload.showDate;
                    }
                }
            }
        } else if (payload.showDate && timelinePosition) {
            if (canFade && typeof motion.fadeText === 'function') {
                motion.fadeText(timelinePosition, payload.showDate);
            } else {
                timelinePosition.textContent = payload.showDate;
            }
        }
    }

    function fetchMetadata(filename) {
        if (metadataRequest) {
            metadataRequest.abort();
            metadataRequest = null;
        }
        var controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
        var url = 'viewer-photo.php?filename=' + encodeURIComponent(filename);
        var options = controller ? { signal: controller.signal } : {};
        metadataRequest = controller;

        return fetch(url, options)
            .then(function(res) {
                if (!res.ok) {
                    throw new Error('Metadata request failed');
                }
                return res.json();
            })
            .then(function(payload) {
                applyMetadata(payload);
                return payload;
            })
            .catch(function(err) {
                if (err && err.name === 'AbortError') {
                    return null;
                }
                return null;
            })
            .finally(function() {
                metadataRequest = null;
            });
    }

    function waitForImagePaint(img) {
        if (!img) {
            return Promise.resolve();
        }
        if (typeof img.decode === 'function') {
            return img.decode().catch(function() {
                return null;
            });
        }
        if (img.complete && img.naturalWidth > 0) {
            return Promise.resolve();
        }
        return new Promise(function(resolve) {
            img.onload = function() { resolve(); };
            img.onerror = function() { resolve(); };
        });
    }

    function crossfadeTo(url, alt) {
        return preloadUrl(url).then(function() {
            var old = currentMainImg;

            // Pin the current photo (and underlay) before the next layer appears,
            // so the white placeholder never shows through the dissolve.
            if (old && old.src) {
                setPlaceholderUnderlay(old.src);
                if (motion && motion.available && motion.gsap) {
                    motion.gsap.killTweensOf(old);
                }
                old.classList.add('loaded');
                old.style.opacity = '1';
                old.style.zIndex = '1';
            }

            var newImg = document.createElement('img');
            newImg.src = url;
            newImg.alt = alt;
            newImg.className = 'viewer-photo-main';
            newImg.style.opacity = '0';
            newImg.style.zIndex = '2';
            placeholder.appendChild(newImg);

            return waitForImagePaint(newImg).then(function() {
                var useMotion = motion && motion.available && typeof motion.crossfadeViewer === 'function'
                    && old && FADE_MS > 0;

                if (useMotion) {
                    return motion.crossfadeViewer(old, newImg, navDirection, FADE_MS).then(function() {
                        currentMainImg = newImg;
                        placeholder.dataset.large = url;
                    });
                }

                return new Promise(function(resolve) {
                    if (old) {
                        old.style.opacity = '1';
                        old.style.zIndex = '1';
                    }

                    requestAnimationFrame(function() {
                        requestAnimationFrame(function() {
                            var finished = false;

                            function finish() {
                                if (finished) {
                                    return;
                                }
                                finished = true;
                                if (old && old.parentNode) {
                                    old.parentNode.removeChild(old);
                                }
                                newImg.classList.remove('is-fading-in');
                                newImg.classList.add('loaded');
                                newImg.style.transition = 'none';
                                newImg.style.opacity = '1';
                                newImg.style.zIndex = '2';
                                setPlaceholderUnderlay(url);
                                currentMainImg = newImg;
                                placeholder.dataset.large = url;
                                resolve();
                            }

                            if (old && FADE_MS > 0) {
                                newImg.classList.add('loaded');
                                newImg.classList.add('is-fading-in');
                                // Clear the pre-insert hide so the CSS opacity transition can run.
                                newImg.style.transition = 'opacity ' + (FADE_MS / 1000) + 's ease-in-out';
                                newImg.style.opacity = '1';
                                newImg.addEventListener('transitionend', function(e) {
                                    if (e.propertyName === 'opacity') {
                                        finish();
                                    }
                                }, { once: true });
                                window.setTimeout(finish, FADE_MS + 80);
                            } else {
                                newImg.classList.add('loaded');
                                newImg.style.opacity = '1';
                                finish();
                            }
                        });
                    });
                });
            });
        });
    }

    function updateHistory(filename, mode) {
        var url = photoPageUrl(filename);
        var state = { filename: filename };
        if (mode === 'replace') {
            history.replaceState(state, '', url);
        } else if (mode === 'push') {
            history.pushState(state, '', url);
        }
    }

    function navigateToFilename(filename, options) {
        options = options || {};
        var index = indexFromFilename(filename);
        if (index < 0 || transitioning) {
            return Promise.resolve(false);
        }
        if (filename === cfg.currentFilename && options.history !== 'replace') {
            return Promise.resolve(false);
        }

        transitioning = true;
        navDirection = index >= cfg.currentIndex ? 1 : -1;
        cfg.currentIndex = index;
        cfg.currentFilename = filename;
        updateNavLinks();

        if (options.history === 'push') {
            updateHistory(filename, 'push');
        } else if (options.history === 'replace') {
            updateHistory(filename, 'replace');
        } else if (options.history === 'none') {
            // URL already matches browser history.
        }

        syncTimelineToFilename(filename);

        var metaPromise = fetchMetadata(filename);
        var fadePromise = crossfadeTo(imageUrl(filename), '');

        return Promise.all([metaPromise, fadePromise]).then(function(results) {
            var payload = results[0];
            if (payload && payload.photoAlt && currentMainImg) {
                currentMainImg.alt = payload.photoAlt;
            }
            return true;
        }).catch(function() {
            if (options.history === 'push') {
                history.back();
            }
            return false;
        }).finally(function() {
            transitioning = false;
        });
    }

    function navigateByOffset(offset, options) {
        var target = filenameFromIndex(cfg.currentIndex + offset);
        if (!target) {
            return Promise.resolve(false);
        }
        options = options || {};
        if (!options.history) {
            options.history = 'push';
        }
        return navigateToFilename(target, options);
    }

    function initInitialPhoto() {
        var largeUrl = placeholder.dataset.large;
        if (!largeUrl) {
            return;
        }

        var alt = placeholder.dataset.alt || '';
        // Start on white; fade the full image in once it is ready.
        placeholder.style.backgroundImage = '';
        preloadUrl(largeUrl).then(function() {
            var imgLarge = document.createElement('img');
            imgLarge.src = largeUrl;
            imgLarge.alt = alt;
            imgLarge.className = 'viewer-photo-main';
            imgLarge.style.opacity = '0';
            placeholder.appendChild(imgLarge);

            function show() {
                imgLarge.classList.add('loaded');
                if (motion && motion.available && motion.gsap && INTRO_FADE_MS > 0) {
                    motion.gsap.killTweensOf(imgLarge);
                    motion.gsap.fromTo(imgLarge, { opacity: 0 }, {
                        opacity: 1,
                        duration: INTRO_FADE_MS / 1000,
                        ease: 'sine.out',
                        onComplete: function () {
                            imgLarge.style.opacity = '1';
                            setPlaceholderUnderlay(largeUrl);
                        }
                    });
                } else {
                    imgLarge.style.opacity = '1';
                    setPlaceholderUnderlay(largeUrl);
                }
            }

            waitForImagePaint(imgLarge).then(show);
            currentMainImg = imgLarge;
            prefetchAdjacent();
        });
    }

    function setDrawerOpen(open) {
        if (!drawer || !toggle) {
            return;
        }
        if (motion && typeof motion.setDetailDrawerOpen === 'function') {
            motion.setDetailDrawerOpen(drawer, toggle, open);
            return;
        }
        drawer.classList.toggle('open', open);
        toggle.classList.toggle('down_arrow', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.setAttribute('aria-label', open ? 'Hide photograph details' : 'Show photograph details');
    }

    if (drawer && toggle) {
        toggle.addEventListener('click', function() {
            setDrawerOpen(!drawer.classList.contains('open'));
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT')) {
            return;
        }
        if (e.key === 'ArrowLeft' && cfg.currentIndex > 0) {
            e.preventDefault();
            navigateByOffset(-1);
        } else if (e.key === 'ArrowRight' && cfg.currentIndex < cfg.filenames.length - 1) {
            e.preventDefault();
            navigateByOffset(1);
        } else if (e.key === 'ArrowUp' && drawer && toggle && !drawer.classList.contains('open')) {
            e.preventDefault();
            setDrawerOpen(true);
        } else if (e.key === 'ArrowDown' && drawer && toggle && drawer.classList.contains('open')) {
            e.preventDefault();
            setDrawerOpen(false);
        }
    });

    if (timelineRange && cfg.timeline && cfg.timeline.entries && cfg.timeline.entries.length > 1) {
        timelineRange.addEventListener('input', function() {
            updateTimelineUi(parseInt(timelineRange.value, 10));
        });

        timelineRange.addEventListener('change', function() {
            var entry = timelineEntry(parseInt(timelineRange.value, 10));
            if (!entry || entry.f === cfg.currentFilename) {
                return;
            }
            navigateToFilename(entry.f, { history: 'push' });
        });

        timelineRange.addEventListener('pointerdown', function(e) {
            e.stopPropagation();
        });

        timelineRange.addEventListener('touchstart', function(e) {
            e.stopPropagation();
        }, { passive: true });

        timelineRange.addEventListener('touchend', function(e) {
            e.stopPropagation();
        }, { passive: true });

        var timelineNav = document.getElementById('viewerTimeline');
        if (timelineNav) {
            timelineNav.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    }

    viewer.addEventListener('click', function(e) {
        var link = e.target.closest('.viewer-photo-prev, .viewer-photo-next');
        if (!link || link.classList.contains('is-hidden')) {
            return;
        }
        e.preventDefault();
        if (link.classList.contains('viewer-photo-prev')) {
            navigateByOffset(-1);
        } else {
            navigateByOffset(1);
        }
    });

    var touchStartX = 0;
    viewer.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    viewer.addEventListener('touchend', function(e) {
        var dx = e.changedTouches[0].screenX - touchStartX;
        if (Math.abs(dx) < 50) {
            return;
        }
        if (dx > 0 && cfg.currentIndex > 0) {
            navigateByOffset(-1);
        } else if (dx < 0 && cfg.currentIndex < cfg.filenames.length - 1) {
            navigateByOffset(1);
        }
    }, { passive: true });

    window.addEventListener('popstate', function() {
        var params = new URLSearchParams(window.location.search);
        var filename = params.get('filename');
        if (!filename) {
            return;
        }
        if (filename === cfg.currentFilename) {
            return;
        }
        navigateToFilename(filename, { history: 'none' });
    });

    if (Array.isArray(cfg.prefetch)) {
        cfg.prefetch.forEach(function(url) {
            preloadUrl(url);
        });
    }

    initInitialPhoto();
    updateNavLinks();
    updateHistory(cfg.currentFilename, 'replace');
})();
