<?php

declare(strict_types=1);

/**
 * Jam slideshow page body + scripts. Required variables in scope before include:
 * - $jam_page_title (string)
 * - $jam_layout ('crop'|'fill') crop = index-style framing; fill = index2-style
 * - $display, $fade (float-ish from GET)
 * - $cdnurl (string)
 * - $array (photo rows from pinchard_jam_photo_list)
 * - $jam_start (int, optional) first index for initial images + JS paging (specific.php)
 */
require_once __DIR__ . '/microsite.php';

$jam_start = isset($jam_start) ? (int) $jam_start : 0;
$layoutFill = isset($jam_layout) && $jam_layout === 'fill';
$slideshowCss = $layoutFill
	? 'width: 110%; position: absolute; top: 0; left: 0;'
	: 'width: 100%; position: absolute; top: -180px;';

$extraHead = '<style>body{margin:0;}#slideshow img{' . $slideshowCss . '}</style>';

pinchard_microsite_head($jam_page_title, ['extra_head' => $extraHead]);

$je = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
?>
    <div id="slideshow" class="slideshow">
<?php
$shown = 0;
for ($i = $jam_start; $i < count($array) && $shown < 10; $i++, $shown++) {
	$photo = $array[$i];
	$src = htmlspecialchars($cdnurl . $photo['filename'], ENT_QUOTES, 'UTF-8');
	echo "            <img src=\"{$src}\" alt=\"\">\n";
}
?>
    </div>

    <script>
        var display = <?= json_encode((float) $display) ?> * 1000;
        var fade = <?= json_encode((float) $fade) ?> * 1000;
        var start = <?= $jam_start ?>;
        var firstImg = null;
        var nextImg = null;
        var imagesArr = <?= json_encode($array, $je) ?>;
        var cdnurl = <?= json_encode($cdnurl, $je) ?>;

        function handleNext(firstImage) {
            nextImg = firstImg.first();
            if (firstImg.next().length > 0) {
                nextImg = firstImg.next();
            } else {
                nextImg = $('.slideshow img').first();
            }
            firstImg.css({ 'z-index': '0', 'display': 'block' });
            nextImg.css({ 'z-index': '1' });
            nextImg.fadeIn(fade, 'linear', function() {
                firstImg.css('display', 'none');
                firstImg = nextImg;
                setTimeout(function() { handleNext(firstImg); }, display);
            });
            var renderedImgsLength = $('.slideshow img').length;
            var firstImageIndex = $('img').index(nextImg);
            if (firstImageIndex === renderedImgsLength - 1) {
                var count = 0;
                for (var i = (firstImageIndex + start); i < imagesArr.length; i++) {
                    if (count === 10) break;
                    $('.slideshow').append("<img style='display:none' src='" + cdnurl + imagesArr[i]['filename'] + "'/>");
                    count++;
                }
            }
        }
        $(document).ready(function() {
            $('.slideshow img').css('display', 'none');
            firstImg = $('.slideshow img').first();
            handleNext(firstImg);
        });
    </script>
<?php
pinchard_microsite_scripts_footer();
