(function($) {
    "use strict";

    // jQuery for page scrolling feature - requires jQuery Easing plugin
    $(document).on('click', 'a.page-scroll', function(event) {
        var $anchor = $(this);
        $('html, body').stop().animate({
            scrollTop: ($($anchor.attr('href')).offset().top - 50)
        }, 1250, 'easeInOutExpo');
        event.preventDefault();
    });

    // Highlight the top nav as scrolling occurs (Bootstrap 5 ScrollSpy)
    if (typeof bootstrap !== 'undefined' && document.body) {
        new bootstrap.ScrollSpy(document.body, {
            target: '#mainNav',
            offset: 51
        });
    }

    // Closes the responsive menu on menu item click
    $('.navbar-collapse ul li a').click(function() {
        $('.navbar-toggler:visible').click();
    });

    // Bootstrap 3 affix was removed in v4; toggle the same class on scroll
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

    // Initialize and Configure Scroll Reveal Animation
    window.sr = ScrollReveal();
    sr.reveal('.sr-icons', {
        duration: 600,
        scale: 0.3,
        distance: '0px'
    }, 200);
    sr.reveal('.sr-button', {
        duration: 1000,
        delay: 200
    });
    sr.reveal('.sr-contact', {
        duration: 600,
        scale: 0.3,
        distance: '0px'
    }, 300);

    // Initialize and Configure Magnific Popup Lightbox Plugin
    $('.popup-gallery').magnificPopup({
        delegate: 'a',
        type: 'image',
        tLoading: 'Loading image #%curr%...',
        mainClass: 'mfp-img-mobile',
        gallery: {
            enabled: true,
            navigateByImgClick: true,
            preload: [0, 1]
        },
        image: {
            tError: '<a href="%url%">The image #%curr%</a> could not be loaded.'
        }
    });

})(jQuery);
