<?php

$display = 5.0;
$fade = 1.0;
if (isset($_GET['display']) && $_GET['display'] !== '') {
	$display = max(0.1, min(600.0, (float) $_GET['display']));
}
if (isset($_GET['fade']) && $_GET['fade'] !== '') {
	$fade = max(0.0, min(60.0, (float) $_GET['fade']));
}

require_once __DIR__ . '/lib/bootstrap.php';
$cfg = pinchard_config();

if (isset($_GET['cury']) && !empty($_GET['cury']) && isset($_GET['curm']) && !empty($_GET['curm'])) {
    $current_year = $_GET['cury'];
    $current_month = $_GET['curm'];
} else {
    $current_month = date('m');
    $current_year = date('Y');
}

$cdnurl = $cfg['cdn_url_thumbnails'];

$array = array();
$supported_image = array(
    'gif',
    'jpg',
    'jpeg',
    'png'
);
$validYearArray = array();
$validMonthArray = array();
$objects = $s3->getIterator('ListObjects', [
    'Bucket' => $cfg['s3_bucket_thumbnails'],
]);

foreach ($objects as $content) {
    if ($content['Key']) {
        $ext = strtolower(pathinfo($content['Key'], PATHINFO_EXTENSION));
        if (in_array($ext, $supported_image)) {
            $dateString = $content['Key'];
            $dateString = explode("_", $dateString)[0];
            // Remove any folder-name and front slash
            if (strrpos($dateString, "/")) {
                $dateString = substr($dateString, strrpos($dateString, "/") + 1);
            }
            $date = DateTime::createFromFormat('Y-m-d\TH:i:s.000\Z', $dateString); //$content->LastModified

            // $date = DateTime::createFromFormat('Y-m-d\TH:i:s.000\Z', $content->LastModified);
            $formatted_date = date_format($date, "Y/m/d H:i:s");
            $year = date_format($date, "Y");
            $month = date_format($date, "m");
            $show_date = pinchard_show_date($date);

            //if(($month == $current_month) && ($year == $current_year)) {
            $array[] = array(
                "filename" => $content['Key'],
                "date" => $formatted_date,
                "show_date" => $show_date
            );
            //}

            $year_month = $year . "-" . $month;
            if (!in_array($year_month, $validMonthArray)) {
                $validMonthArray[] = $year_month;
            }
        }
    }
}
usort($array, function ($a, $b) {
    return $a['date'] <=> $b['date'];
});

?>
<script src="vendor/jquery/jquery.js"></script>
<script type="text/javascript" src="vendor/slick/slick.js"></script>

<link rel="stylesheet" type="text/css" href="vendor/slick/slick.css" />

<style>
    #slideshow img {
        width: 100%;
        position: absolute;
    }
</style>
<div id="slideshow" class="slideshow">
    <?php
    for ($i = 0; $i < count($array); $i++) {
        $photo = $array[$i];
        if ($i == 10) {
            break;
        }
    ?>
        <img src="<?php echo htmlspecialchars($cdnurl . $photo['filename'], ENT_QUOTES, 'UTF-8') ?>" alt="">
    <?php } ?>


</div>


<script>
    var display = <?= json_encode($display) ?> * 1000;
    var fade = <?= json_encode($fade) ?> * 1000;
    var firstImg = null;
    var nextImg = null

    var imagesArr = <?= json_encode($array, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var cdnurl = <?= json_encode($cdnurl, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var currentIndex = 3;

    function handleNext(firstImage) {
        nextImg = firstImg.first();
        if (firstImg.next().length > 0) {
            nextImg = firstImg.next();
        } else {
            nextImg = $('.slideshow img').first();
        }

        firstImg.css({
            'z-index': '0',
            'display': 'block'
        });

        nextImg.css({
            'z-index': '1',

        })
        nextImg.fadeIn(fade, 'linear', function() {
            firstImg.css('display', 'none');
            firstImg = nextImg;
            setTimeout(function() {
                handleNext(firstImg);
            }, display)

        });
        var renderedImgsLength = $('.slideshow img').length
        var firstImageIndex = $('img').index(nextImg)

        if (firstImageIndex == renderedImgsLength - 1) {
            // load next images if any
            var count = 0;
            for (i = firstImageIndex; i < imagesArr.length; i++) {
                if (count == 10) {
                    break;
                }
                $('.slideshow').append($('<img>', {
                    style: 'display:none',
                    src: cdnurl + imagesArr[i]['filename']
                }));
                count++
            }
        }
    }
    $(document).ready(function() {

        $('.slideshow img').css('display', 'none');
        firstImg = $('.slideshow img').first();
        handleNext(firstImg);

    });
</script>