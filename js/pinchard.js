(function($) {
    "use strict";

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

})(jQuery);
