(function($) {
    "use strict";

    var ROUTE_LEAVE_MS = 220;
    var MAIN_ROUTES = {
        'index.php': true,
        'gallery.php': true,
        'slideshow.php': true,
        'info.php': true
    };

    function prefersReducedMotion() {
        return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }

    function routePathname(pathname) {
        var segments = pathname.split('/').filter(Boolean);
        var base = segments.length ? segments[segments.length - 1] : 'index.php';
        if (base.indexOf('.') === -1) {
            return 'index.php';
        }
        return base;
    }

    function isMainRouteHref(href) {
        try {
            var url = new URL(href, window.location.href);
            if (url.origin !== window.location.origin) {
                return false;
            }
            var base = routePathname(url.pathname);
            return MAIN_ROUTES[base] === true;
        } catch (err) {
            return false;
        }
    }

    function isMainNavLink(link) {
        return !!(link && link.closest && link.closest('#mainNav') && isMainRouteHref(link.href));
    }

    function navigateWithTransition(href) {
        if (prefersReducedMotion() || !href) {
            window.location.href = href;
            return;
        }
        document.body.classList.add('pinchard-route-leave');
        document.body.classList.remove('pinchard-route-enter', 'is-ready');
        window.setTimeout(function() {
            window.location.href = href;
        }, ROUTE_LEAVE_MS);
    }

    window.pinchardNavigate = navigateWithTransition;

    if (!prefersReducedMotion()) {
        var skipRouteEnter = document.body.classList.contains('maps-satellite-page')
            || document.body.classList.contains('maps-embed-page');

        if (!skipRouteEnter) {
            document.body.classList.add('pinchard-route-enter');
            window.requestAnimationFrame(function() {
                window.requestAnimationFrame(function() {
                    document.body.classList.add('is-ready');
                });
            });
        }

        document.addEventListener('click', function(event) {
            if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }
            var link = event.target.closest('a[href]');
            if (!link || link.target === '_blank' || link.hasAttribute('download') || !isMainNavLink(link)) {
                return;
            }
            var nextUrl = new URL(link.href, window.location.href);
            if (nextUrl.href === window.location.href) {
                return;
            }
            event.preventDefault();
            navigateWithTransition(link.href);
        });
    }

    $(document).on('click', 'a.page-scroll', function(event) {
        var $anchor = $(this);
        $('html, body').stop().animate({
            scrollTop: ($($anchor.attr('href')).offset().top - 50)
        }, 1250, 'easeInOutExpo');
        event.preventDefault();
    });

    $('.navbar-collapse ul li a').click(function() {
        $('.navbar-toggler:visible').click();
    });

    var $mainNav = $('#mainNav');
    if ($mainNav.length) {
        var affixOffset = 100;
        $(window).on('scroll', function() {
            if ($(window).scrollTop() > affixOffset) {
                $mainNav.addClass('affix');
            } else {
                $mainNav.removeClass('affix');
            }
        }).trigger('scroll');
    }

    function closeMapsDropdowns() {
        $('.maps-nav-dropdown.is-open').removeClass('is-open')
            .find('.maps-nav-dropdown-trigger').attr('aria-expanded', 'false');
    }

    var mapsDropdownCloseTimer = null;
    var mapsDropdownHover = window.matchMedia('(hover: hover) and (pointer: fine)');

    $('.maps-nav-dropdown').on('mouseenter', function() {
        if (!mapsDropdownHover.matches) {
            return;
        }
        window.clearTimeout(mapsDropdownCloseTimer);
        closeMapsDropdowns();
        $(this).addClass('is-open').find('.maps-nav-dropdown-trigger').attr('aria-expanded', 'true');
    }).on('mouseleave', function() {
        if (!mapsDropdownHover.matches) {
            return;
        }
        var $dropdown = $(this);
        window.clearTimeout(mapsDropdownCloseTimer);
        mapsDropdownCloseTimer = window.setTimeout(function() {
            $dropdown.removeClass('is-open').find('.maps-nav-dropdown-trigger').attr('aria-expanded', 'false');
        }, 120);
    });

    $(document).on('click', '.maps-nav-dropdown-trigger', function(event) {
        event.preventDefault();
        event.stopPropagation();
        var $dropdown = $(this).closest('.maps-nav-dropdown');
        var isOpen = $dropdown.hasClass('is-open');
        closeMapsDropdowns();
        if (!isOpen) {
            $dropdown.addClass('is-open');
            $(this).attr('aria-expanded', 'true');
        }
    });

    $(document).on('click', function(event) {
        if (!$(event.target).closest('.maps-nav-dropdown').length) {
            closeMapsDropdowns();
        }
    });

    $(document).on('keydown', function(event) {
        if (event.key === 'Escape') {
            closeMapsDropdowns();
        }
    });

    $(document).on('click', '.citation-copy-btn', function() {
        var $btn = $(this);
        var text = $btn.attr('data-citation') || '';
        if (!text) {
            return;
        }

        function markCopied() {
            var original = $btn.data('copy-label') || 'Copy';
            $btn.addClass('is-copied').text('Copied');
            window.setTimeout(function() {
                $btn.removeClass('is-copied').text(original);
            }, 2000);
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(markCopied).catch(function() {
                fallbackCopy(text, markCopied);
            });
            return;
        }

        fallbackCopy(text, markCopied);
    });

    function fallbackCopy(text, onSuccess) {
        var textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.setAttribute('readonly', '');
        textarea.style.position = 'absolute';
        textarea.style.left = '-9999px';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            if (document.execCommand('copy')) {
                onSuccess();
            }
        } finally {
            document.body.removeChild(textarea);
        }
    }

})(jQuery);
