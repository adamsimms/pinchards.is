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
header('X-Robots-Tag: noindex, nofollow');
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
<style>
    #slideshow {
        position: relative;
        width: 100%;
        height: 100vh;
    }
    #slideshow img {
        width: 100%;
        position: absolute;
        inset: 0;
        opacity: 0;
        transition: opacity var(--fade-ms, 1s) linear;
    }
    #slideshow img.is-active {
        opacity: 1;
        z-index: 1;
    }
</style>
<div id="slideshow" class="slideshow" style="--fade-ms: <?= pinchard_h((string) $fade) ?>s">
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
(function() {
    var displayMs = <?= json_encode($display) ?> * 1000;
    var fadeMs = <?= json_encode($fade) ?> * 1000;
    var imagesArr = <?= json_encode($array, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var cdnurl = <?= json_encode($cdnurl, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var slideshow = document.getElementById('slideshow');
    if (!slideshow || !imagesArr.length) {
        return;
    }

    var renderedCount = Math.min(10, imagesArr.length);
    var currentIndex = 0;
    var currentImg = slideshow.querySelector('img');

    function appendImage(index) {
        if (index >= imagesArr.length || slideshow.querySelectorAll('img').length >= renderedCount + 10) {
            return;
        }
        var img = document.createElement('img');
        img.src = cdnurl + imagesArr[index].filename;
        img.alt = '';
        img.hidden = true;
        slideshow.appendChild(img);
    }

    function showNext() {
        var imgs = slideshow.querySelectorAll('img');
        var activeIndex = Array.prototype.indexOf.call(imgs, currentImg);
        var nextIndex = activeIndex + 1;
        if (nextIndex >= imgs.length) {
            nextIndex = 0;
        }
        var nextImg = imgs[nextIndex];
        if (!nextImg) {
            return;
        }

        nextImg.hidden = false;
        nextImg.classList.add('is-active');
        currentImg.classList.remove('is-active');
        window.setTimeout(function() {
            currentImg.hidden = true;
            currentImg = nextImg;
            currentIndex = (currentIndex + 1) % imagesArr.length;
            if (activeIndex === imgs.length - 1) {
                for (var i = 0; i < 10; i++) {
                    appendImage(imgs.length + i);
                }
            }
            window.setTimeout(showNext, displayMs);
        }, fadeMs);
    }

    if (currentImg) {
        currentImg.classList.add('is-active');
        currentImg.hidden = false;
        window.setTimeout(showNext, displayMs);
    }
})();
</script>