<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$display = 0.01;
$fade = 6;

if (isset($_GET['display']) && !empty($_GET['display'])) {
    $display = $_GET['display'];
}
if (isset($_GET['fade']) && !empty($_GET['fade'])) {
    $fade = $_GET['fade'];
}

include('../functions_inc.php');
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
shuffle($array);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<script src="../vendor/jquery/jquery.js"></script>
<script src="../vendor/jquery-ui/jquery-ui.js"></script>
<script src="../vendor/jquery/jquery.js"></script>
<script type="text/javascript" src="../vendor/slick/slick.js"></script>

<link rel="stylesheet" type="text/css" href="../vendor/slick/slick.css" />

<style>
    body {
      margin: 0;
    }
    #slideshow img {
        width: 100%;
        position: absolute;
        top: -180px;
    }
</style>

<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="">
<meta name="author" content="">

<title>Cloudberry Jam</title>

<!-- Bootstrap Core CSS -->
<link href="../vendor/bootstrap/css/bootstrap.css" rel="stylesheet">

<!-- Custom Fonts -->
<!-- <link href="..vendor/font-awesome/css/font-awesome.css" rel="stylesheet" type="text/css"> -->
<!-- <link href='https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'> -->
<!-- <link href='https://fonts.googleapis.com/css?family=Merriweather:400,300,300italic,400italic,700,700italic,900,900italic' rel='stylesheet' type='text/css'> -->

<!-- Theme CSS -->
<link href="..css/pinchard.css" rel="stylesheet">

<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.js"></script>
<![endif]-->

<!-- Favicon -->
<link rel="apple-touch-icon" sizes="180x180" href="../favicon/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="../favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../favicon/favicon-16x16.png">
<link rel="manifest" href="../favicon/manifest.json">
<link rel="mask-icon" href="../favicon/safari-pinned-tab.svg" color="#5bbad5">
<link rel="shortcut icon" href="../favicon/favicon.ico">
<meta name="msapplication-config" content="../favicon/browserconfig.xml">
<meta name="theme-color" content="#ffffff">

</head>
  <body>
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

    <!-- jQuery -->
    <script src="../vendor/jquery/jquery.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="../vendor/bootstrap/js/bootstrap.js"></script>

    <!-- Plugin JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.js"></script>
    <!-- <script src="vendor/scrollreveal/scrollreveal.js"></script> -->
    <!-- <script src="vendor/magnific-popup/jquery.magnific-popup.js"></script> -->

    <!-- Theme JavaScript -->
    <script src="../js/pinchard.js"></script>

    <!-- <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_BROWSER_KEY&callback=myMap"></script> -->

    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-G1XKSQNT5M"></script>
    <script>
         window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
         gtag('js', new Date());

         gtag('config', 'G-G1XKSQNT5M');
    </script>

  </body>
</html>
