(function() {
    "use strict";

    var cfg = window.pinchardSlideshow;
    if (!cfg || !cfg.images || !cfg.images.length) {
        var empty = document.getElementById("slideshow");
        if (empty) {
            empty.classList.add("pinchard-empty-state");
            empty.textContent = "No photographs available.";
            if (window.pinchardMotion && typeof window.pinchardMotion.fadeInElement === "function") {
                window.pinchardMotion.fadeInElement(empty, 0.7);
            }
        }
        return;
    }

    var container = document.getElementById("slideshow");
    if (!container) {
        return;
    }

    var timelineRange = document.getElementById("viewerTimelineRange");
    var timelinePosition = document.getElementById("viewerTimelinePosition");
    var startIndex = typeof cfg.startIndex === "number" ? cfg.startIndex : 0;
    if (startIndex < 0) {
        startIndex = 0;
    }
    if (startIndex >= cfg.images.length) {
        startIndex = 0;
    }

    var index = startIndex;
    var currentImg = null;
    var fadingOutImg = null;
    var advanceTimer = null;
    var paused = false;
    var scrubbing = false;
    var crossfading = false;
    var navToggle = document.getElementById("navSlideshowToggle");
    var motion = window.pinchardMotion;
    var reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
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
            navToggle.classList.toggle("is-paused", paused);
            navToggle.setAttribute("aria-label", paused ? "Resume slideshow" : "Pause slideshow");
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
        timelineRange.setAttribute("aria-valuenow", String(position));
        timelineRange.setAttribute("aria-valuetext", "Photograph " + position + " of " + count + ", " + entry.d);
        if (timelinePosition) {
            if (motion && typeof motion.fadeText === "function") {
                motion.fadeText(timelinePosition, entry.d);
            } else {
                timelinePosition.textContent = entry.d;
            }
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

        var img = document.createElement("img");
        img.className = "slideshow-photo";
        img.src = photoUrl(i);
        img.alt = cfg.images[i].show_date || "";
        container.appendChild(img);

        preloadIndex((i + 1) % cfg.images.length);

        if (!currentImg) {
            img.style.transition = "none";
            img.classList.add("is-visible");
            requestAnimationFrame(function() {
                img.style.transition = "";
            });
            currentImg = img;
            scheduleAdvance();
            return;
        }

        crossfading = true;
        clearAdvanceTimer();
        fadingOutImg = currentImg;
        fadingOutImg.classList.add("is-fading-out");
        fadingOutImg.classList.remove("is-visible");

        if (fadeMs === 0) {
            removeImage(fadingOutImg);
            fadingOutImg = null;
            img.classList.add("is-visible");
            currentImg = img;
            crossfading = false;
            scheduleAdvance();
            return;
        }

        var useMotion = motion && motion.available && motion.gsap;
        if (useMotion) {
            motion.gsap.set(img, { opacity: 0 });
            img.classList.add("is-visible");
            motion.gsap.to(img, {
                opacity: 1,
                duration: fadeMs / 1000,
                ease: "power1.inOut"
            });
            if (fadingOutImg) {
                motion.gsap.to(fadingOutImg, {
                    opacity: 0,
                    duration: fadeMs / 1000,
                    ease: "power1.inOut"
                });
            }
            window.setTimeout(function() {
                finishCrossfade(img);
            }, fadeMs + 40);
            return;
        }

        requestAnimationFrame(function() {
            requestAnimationFrame(function() {
                img.classList.add("is-visible");
            });
        });

        var settled = false;
        function onFadeComplete(e) {
            if (settled || e.target !== img || e.propertyName !== "opacity") {
                return;
            }
            settled = true;
            img.removeEventListener("transitionend", onFadeComplete);
            finishCrossfade(img);
        }

        img.addEventListener("transitionend", onFadeComplete);
        window.setTimeout(function() {
            if (!settled) {
                settled = true;
                img.removeEventListener("transitionend", onFadeComplete);
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

    showIndex(startIndex, { force: true });

    if (navToggle) {
        navToggle.addEventListener("click", function() {
            setPaused(!paused);
        });
    }

    if (timelineRange && cfg.timeline && cfg.timeline.entries && cfg.timeline.entries.length > 1) {
        timelineRange.addEventListener("input", function() {
            updateTimelineUi(parseInt(timelineRange.value, 10));
        });

        timelineRange.addEventListener("change", function() {
            var nextIndex = parseInt(timelineRange.value, 10);
            showIndex(nextIndex, { force: true });
            if (!paused) {
                scheduleAdvance();
            }
        });

        timelineRange.addEventListener("pointerdown", function() {
            scrubbing = true;
            clearAdvanceTimer();
        });

        timelineRange.addEventListener("pointerup", function() {
            scrubbing = false;
            if (!paused && !crossfading) {
                scheduleAdvance();
            }
        });

        timelineRange.addEventListener("pointercancel", function() {
            scrubbing = false;
            if (!paused && !crossfading) {
                scheduleAdvance();
            }
        });
    }

    function isTypingTarget(el) {
        if (!el || !el.tagName) {
            return false;
        }
        var tag = el.tagName;
        return tag === "INPUT" || tag === "TEXTAREA" || tag === "SELECT" || el.isContentEditable;
    }

    function goRelative(delta) {
        var next = (index + delta + cfg.images.length) % cfg.images.length;
        showIndex(next, { force: true });
    }

    var canToggleKiosk = document.body.classList.contains("slideshow-page");
    var kioskActive = document.body.classList.contains("slideshow-page--kiosk");

    function syncKioskUrl(active) {
        try {
            var url = new URL(window.location.href);
            if (active) {
                url.searchParams.set("kiosk", "1");
            } else {
                url.searchParams.delete("kiosk");
            }
            window.history.replaceState(null, "", url.pathname + url.search + url.hash);
        } catch (err) {
            /* ignore */
        }
    }

    function requestBrowserFullscreen() {
        var el = document.documentElement;
        var req = el.requestFullscreen || el.webkitRequestFullscreen || el.msRequestFullscreen;
        if (req) {
            try {
                var result = req.call(el);
                if (result && typeof result.catch === "function") {
                    result.catch(function() { /* user denied or unavailable */ });
                }
            } catch (err) {
                /* ignore */
            }
        }
    }

    function exitBrowserFullscreen() {
        if (!document.fullscreenElement && !document.webkitFullscreenElement) {
            return;
        }
        var exit = document.exitFullscreen || document.webkitExitFullscreen || document.msExitFullscreen;
        if (exit) {
            try {
                var result = exit.call(document);
                if (result && typeof result.catch === "function") {
                    result.catch(function() { /* ignore */ });
                }
            } catch (err) {
                /* ignore */
            }
        }
    }

    function setKiosk(active, options) {
        options = options || {};
        if (!canToggleKiosk || active === kioskActive) {
            return;
        }
        kioskActive = active;
        document.body.classList.toggle("slideshow-page--kiosk", active);
        syncKioskUrl(active);
        if (options.browserFullscreen !== false) {
            if (active) {
                requestBrowserFullscreen();
            } else {
                exitBrowserFullscreen();
            }
        }
    }

    function toggleKiosk() {
        setKiosk(!kioskActive);
    }

    document.addEventListener("fullscreenchange", function() {
        if (!canToggleKiosk) {
            return;
        }
        if (!document.fullscreenElement && kioskActive) {
            setKiosk(false, { browserFullscreen: false });
        }
    });
    document.addEventListener("webkitfullscreenchange", function() {
        if (!canToggleKiosk) {
            return;
        }
        if (!document.webkitFullscreenElement && kioskActive) {
            setKiosk(false, { browserFullscreen: false });
        }
    });

    document.addEventListener("keydown", function(e) {
        if (isTypingTarget(e.target)) {
            return;
        }

        if (e.key === "Escape") {
            if (canToggleKiosk && kioskActive) {
                e.preventDefault();
                setKiosk(false);
                return;
            }
            var galleryHref = "/gallery.php";
            if (window.pinchardNavigate) {
                window.pinchardNavigate(galleryHref);
            } else {
                window.location.href = galleryHref;
            }
            return;
        }

        if (canToggleKiosk && (e.key === "f" || e.key === "F")) {
            e.preventDefault();
            toggleKiosk();
            return;
        }

        if (e.key === " " || e.key === "Spacebar") {
            e.preventDefault();
            setPaused(!paused);
            return;
        }

        if (e.key === "ArrowLeft") {
            e.preventDefault();
            goRelative(-1);
            return;
        }

        if (e.key === "ArrowRight") {
            e.preventDefault();
            goRelative(1);
        }
    });
})();
