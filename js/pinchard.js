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
