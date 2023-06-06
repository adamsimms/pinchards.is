<?php
require_once 'functions_inc.php';

if (isset($_GET['cury'], $_GET['curm']) && !empty($_GET['cury']) && !empty($_GET['curm'])) {
    $current_year = $_GET['cury'];
    $current_month = $_GET['curm'];
} else {
    $current_month = date('m');
    $current_year = date('Y');
}

ini_set('allow_url_fopen', 'on');
//$cdnurl = 'http://d3kq73uimqeic8.cloudfront.net/';
$cdnurl = 'http://d35wkpjsrmtk40.cloudfront.net/';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $cdnurl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$xmlresponse = curl_exec($ch);
$xml = simplexml_load_string($xmlresponse);

$array = [];
$supported_image = ['gif', 'jpg', 'jpeg', 'png'];
$validYearArray = [];
$validMonthArray = [];
$objects = $s3->getIterator('ListObjects', [
    'Bucket' => 'shutter-island-thumbnails',
]);

foreach ($objects as $content) {
    if ($content['Key']) {
        $ext = strtolower(pathinfo($content['Key'], PATHINFO_EXTENSION));
        if (in_array($ext, $supported_image)) {
            $dateString = $content['Key'];
            $dateString = explode("_", $dateString)[0];
            if (strrpos($dateString, "/")) {
                $dateString = substr($dateString, strrpos($dateString, "/") + 1);
            }
            //$date = DateTime::createFromFormat('Y-m-d\TH:i:s.000\Z', $dateString);
            $date = (new DateTime)->setTimestamp(strtotime($dateString));
            $formatted_date = date_format($date, "Y/m/d H:i:s");
            $year = date_format($date, "Y");
            $month = date_format($date, "m");
            $show_date = date_format($date, "M j @ H:i");

            $array[] = [
                "filename" => $content['Key'],
                "date" => $formatted_date,
                "show_date" => $show_date,
            ];

            $year_month = $year . "-" . $month;
            if (!in_array($year_month, $validMonthArray)) {
                $validMonthArray[] = $year_month;
            }
        }
    }
}

usort($array, fn ($a, $b) => $a['date'] <=> $b['date']);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Pinchard's Island</title>

    <link href="vendor/jquery-ui/jquery-ui.css" rel="stylesheet">

    <!-- Bootstrap Core CSS -->
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800' rel='stylesheet' type='text/css'>
    <link href='https://fonts.googleapis.com/css?family=Merriweather:400,300,300italic,400italic,700,700italic,900,900italic' rel='stylesheet' type='text/css'>

    <!-- Plugin CSS -->
    <link href="vendor/magnific-popup/magnific-popup.css" rel="stylesheet">

    <!-- Theme CSS -->
    <link href="css/pinchard.css" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <script src="vendor/exifjs/exif.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/lozad/dist/lozad.min.js"></script>


    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">
    <link rel="manifest" href="/favicon/manifest.json">
    <link rel="mask-icon" href="/favicon/safari-pinned-tab.svg" color="#5bbad5">
    <link rel="shortcut icon" href="/favicon/favicon.ico">
    <meta name="msapplication-config" content="/favicon/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">

    <style>
        .content_area {
            padding: 32px 0px 8px 0px !important;
            border: none;
        }

        #photo_container {
            padding: 0px;
            margin-top: 0px;
            border: none;
        }

        #photo_container>.row {
            margin: 0px !important;
            border: none;
        }

        .photos {
            width: 100% !important;
            border: none;
        }

        .photoElement {
            padding: 0px;
            margin-bottom: 0px;
            border: none;
        }

        .photoElement a {
            border-radius: 0px;
            border: none;
        }

        img.lazy {
            background-image: url('./img/loading.gif');
            background-repeat: no-repeat;
            background-position: 50% 50%;
            background-size: 18px 18px;
            border: none;
        }


        img.lazy {
            animation: fadein 4s;
            -moz-animation: fadein 4s;
            /* Firefox */
            -webkit-animation: fadein 4s;
            /* Safari and Chrome */
            -o-animation: fadein 4s;
            /* Opera */
        }


        @keyframes fadein {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @-moz-keyframes fadein {

            /* Firefox */
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @-webkit-keyframes fadein {

            /* Safari and Chrome */
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @-o-keyframes fadein {

            /* Opera */
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }
    </style>

</head>

<!--<body id="page-top" onload="myFunction()" >-->

<body id="page-top">
    <nav id="mainNav" class="navbar navbar-default navbar-fixed-top">
        <a href="gallery.php" class="link-to-gallery nav_cloudberry active"></a>
        <a class="nav_info" href="info.php"></a>
        <div class="title">
            <a href="index.php">pinchards.is</a>
        </div>
    </nav>
    <div class="content_area">
        <div class="container" id="photo_container">
            <div class="row">
                <div class="photos" id="photos">
                    <?php foreach ($array as $photo) : ?>
                        <div class="col-md-5ths col-sm-6 col-xs-12 photoElement">
                            <a href="index.php?fn=<?php echo $photo['filename'] ?>" class="photoBox">
                                <img class="lazy" data-src="<?php echo $cdnurl . $photo['filename'] ?>" class="img-responsive" alt="" width="288" height="224">
                                <div class="photo-box-caption">
                                    <div class="photo-box-caption-content"><?php echo $photo['show_date'] ?></div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/jquery-ui/jquery-ui.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Plugin JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js"></script>
    <script src="vendor/scrollreveal/scrollreveal.min.js"></script>
    <script src="vendor/magnific-popup/jquery.magnific-popup.min.js"></script>

    <!-- Theme JavaScript -->
    <script src="js/pinchard.min.js"></script>
    <script src="js/jquery.lazy.min.js"></script>

    <script>
        $(function() {
            $('.lazy').Lazy({
                // your configuration goes here
                scrollDirection: 'vertical',
                effect: 'fadeIn',
                visibleOnly: true,
                beforeLoad: function(element) {
                    //console.log('loading... ', element.data('src'))
                },
                onError: function(element) {
                    console.log('error loading ' + element.data('src'));
                }
            });
        });
    </script>

</body>

</html>