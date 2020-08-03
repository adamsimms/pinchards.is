<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


$display = 5;
$fade = 1;
if (isset($_GET['display']) && !empty($_GET['display'])) {
    $display = $_GET['display'];
}
if (isset($_GET['fade']) && !empty($_GET['fade'])) {
    $fade = $_GET['fade'];
}

include('functions_inc.php');
if (isset($_GET['cury']) && !empty($_GET['cury']) && isset($_GET['curm']) && !empty($_GET['curm'])) {
    $current_year = $_GET['cury'];
    $current_month = $_GET['curm'];
} else {
    $current_month = date('m');
    $current_year = date('Y');
    // echo "phpcurr_month = " . $current_month;
}
ini_set('allow_url_fopen', 'on');
$cdnurl = 'http://d3kq73uimqeic8.cloudfront.net/';
//$cdnurl = 'http://d35wkpjsrmtk40.cloudfront.net/';
// $xml = simplexml_load_file($cdnurl) or die("Error: Cannot create object");
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $cdnurl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$xmlresponse = curl_exec($ch);
$xml = simplexml_load_string($xmlresponse);

$array = array();
$supported_image = array(
    'gif',
    'jpg',
    'jpeg',
    'png'
);
$validYearArray = array();
$validMonthArray = array();
$objects = $s3->getIterator('ListObjects', array(
    "Bucket" => "shutter-island-thumbnails",
    //"Prefix" => ''
));

foreach ($objects as $content) {
    //foreach($xml->children() as $content) {
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
            $show_date = date_format($date, "M j @ H:i");

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
    return $a['date'] > $b['date'];
});

//var_dump($array)

?>
<script src=" vendor/jquery/jquery.min.js"></script>
<script src="vendor/jquery-ui/jquery-ui.min.js"></script>
<script src=" vendor/jquery/jquery.min.js"></script>
<script type="text/javascript" src="vendor/slick/slick.min.js"></script>

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
        <img src="<?php echo $cdnurl . $photo['filename'] ?>">
    <?php } ?>


</div>


<script>
    var display = <?php echo $display; ?> * 1000; // duration in seconds
    var fade = <?php echo $fade; ?> * 1000; // fade duration amount relative to the time the image is visible
    console.log("display=> ", display, "fade => ", fade)
    var firstImg = null;
    var nextImg = null

    var imagesArr = <?php echo json_encode($array) ?>;
    var cdnurl = "<?php echo $cdnurl ?>";
    var currentIndex = 3;

    console.log('total images ', imagesArr)

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
        console.log('renderedImgsLength =>', renderedImgsLength);
        console.log('firstImageIndex => ', firstImageIndex);

        if (firstImageIndex == renderedImgsLength - 1) {
            // load next images if any
            console.log('adding new images')
            var count = 0;
            for (i = firstImageIndex; i < imagesArr.length; i++) {
                if (count == 10) {
                    break;
                }
                $('.slideshow').append("<img style='display:none'  src='" + cdnurl + imagesArr[i]['filename'] + "'/>");
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