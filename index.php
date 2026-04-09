<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Pinchard's Island</title>

    <!-- Bootstrap Core CSS -->
    <link href="vendor/bootstrap/css/bootstrap.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="vendor/font-awesome/css/font-awesome.css" rel="stylesheet" type="text/css">
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
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.js"></script>
    <![endif]-->

    <script src="vendor/exifjs/exif.js"></script>

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">
    <link rel="manifest" href="/favicon/manifest.json">
    <link rel="mask-icon" href="/favicon/safari-pinned-tab.svg" color="#5bbad5">
    <link rel="shortcut icon" href="/favicon/favicon.ico">
    <meta name="msapplication-config" content="/favicon/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">

</head>

<body id="page-top">
    <?php

    ini_set('allow_url_fopen', 'on');
    require_once __DIR__ . '/lib/bootstrap.php';
    $cfg = pinchard_config();

    if (isset($_GET['fn']) && !empty($_GET['fn'])) {
        $filename = $_GET['fn'];
    }

    $array = getObjectList($cfg['s3_bucket_full']);
    usort($array, fn ($a, $b) => $a['date'] <=> $b['date']);

    if ($array === []) {
        http_response_code(503);
        exit('Photo gallery is empty or temporarily unavailable.');
    }

    // get prev and next filename
    if (isset($filename) && !empty($filename) && $filename != "") {
        foreach ($array as $i => $content) {
            if ($content['filename'] == $filename) {
                if ($i > 0) {
                    $prev_filename = $array[$i - 1]['filename'];
                }
                if ($i < count($array) - 1) {
                    $next_filename = $array[$i + 1]['filename'];
                }
                $datetime = $content['date'];
                break;
            }
        }
    } else {
        //get last element of filename
        $content = end($array);
        $filename = $content['filename'];
        $datetime = $content['date'];
        if (count($array) > 1 && (!isset($prev_filename) || empty($prev_filename) || $prev_filename == "")) {
            $prev_filename = $array[count($array) - 2]['filename'];
        }
    }

    $result_1 = $s3->getObject([
        'Bucket' => $cfg['s3_bucket_full'],
        'Key' => $filename,
        'SaveAs' => __DIR__ . '/images/photo/tmp.jpg',
    ]);

    $exif = exif_read_data(__DIR__ . '/images/photo/tmp.jpg', 0, true);

    $make = $exif['IFD0']['Make'] ?? '';
    $model = $exif['IFD0']['Model'] ?? '';
    $focal_length = $exif['EXIF']['FocalLength'] ?? '';
    $exposure_time = $exif['EXIF']['ExposureTime'] ?? '';
    $fnumber = $exif['EXIF']['FNumber'] ?? '';
    $iso_speed_ratings = $exif['EXIF']['ISOSpeedRatings'] ?? '';

    $image_width = $exif['COMPUTED']['Width'] ?? $exif['EXIF']['ExifImageWidth'] ?? '';
    $image_height = $exif['IFD0']['Height'] ?? $exif['EXIF']['ExifImageLength'] ?? '';

    $xresolution = $exif['IFD0']['XResolution'] ?? $exif['THUMBNAIL']['XResolution'] ?? '';
    $yresolution = $exif['IFD0']['YResolution'] ?? $exif['THUMBNAIL']['YResolution'] ?? '';

    $flash = $exif['EXIF']['Flash'] ?? '';
    $str_flash = "";

    if ($flash != '') {
        $flashValues = [
            0x0 => "No Flash",
            0x1 => "Fired",
            0x5 => "Fired, Return not detected",
            0x7 => "Fired, Return detected",
            0x8 => "On, Did not fire",
            0x9 => "On, Fired",
            0xd => "On, Return not detected",
            0xf => "On, Return detected",
            0x10 => "Off, Did not fire",
            0x14 => "Off, Did not fire, Return not detected",
            0x18 => "Auto, Did not fire",
            0x19 => "Auto, Fired",
            0x1d => "Auto, Fired, Return not detected",
            0x1f => "Auto, Fired, Return detected",
            0x20 => "No flash function",
            0x30 => "Off, No flash function",
            0x41 => "Fired, Red-eye reduction",
            0x45 => "Fired, Red-eye reduction, Return not detected",
            0x47 => "Fired, Red-eye reduction, Return detected",
            0x49 => "On, Red-eye reduction",
            0x4d => "On, Red-eye reduction, Return not detected",
            0x4f => "On, Red-eye reduction, Return detected",
            0x50 => "Off, Red-eye reduction",
            0x58 => "Auto, Did not fire, Red-eye reduction",
            0x59 => "Auto, Fired, Red-eye reduction",
            0x5d => "Auto, Fired, Red-eye reduction, Return not detected",
            0x5f => "Auto, Fired, Red-eye reduction, Return detected",
        ];

        $str_flash = $flashValues[$flash] ?? "";
    }

    $software = $exif['IFD0']['Software'] ?? '';

    $gps_latitude_degree = $gps_latitude_min = $gps_latitude_sec = "";
    $gps_longitude_degree = $gps_longitude_min = $gps_longitude_sec = "";

    $gps_latitude_ref = $exif['GPS']['GPSLatitudeRef'] ?? '';
    $gps_longitude_ref = $exif['GPS']['GPSLongitudeRef'] ?? '';
    $gps_altitude = $exif['GPS']['GPSAltitude'] ?? '';
    $lon = "";
    $lat = "";

    if (isset($exif['GPS']['GPSLatitude'])) {
        $gps_latitude_array = $exif['GPS']['GPSLatitude'];
        $gps_latitude_degree = explode('/', $gps_latitude_array[0])[0];
        $gps_latitude_min = explode('/', $gps_latitude_array[1])[0];
        $gps_latitude_sec = number_format(explode('/', $gps_latitude_array[2])[0] / explode('/', $gps_latitude_array[2])[1], 2);
    }

    if (isset($exif['GPS']['GPSLongitude'])) {
        $gps_longitude_array = $exif['GPS']['GPSLongitude'];
        $gps_longitude_degree = explode('/', $gps_longitude_array[0])[0];
        $gps_longitude_min = explode('/', $gps_longitude_array[1])[0];
        $gps_longitude_sec = number_format(explode('/', $gps_longitude_array[2])[0] / explode('/', $gps_longitude_array[2])[1], 2);
    }

    if (isset($exif['GPS']['GPSLatitude']) && isset($exif['GPS']['GPSLongitude'])) {
        $lon = getGps($exif['GPS']["GPSLongitude"], $exif['GPS']['GPSLongitudeRef']);
        $lat = getGps($exif['GPS']["GPSLatitude"], $exif['GPS']['GPSLatitudeRef']);
    }

    function getGps(array $exifCoord, string $hemi): float
    {
        $degrees = count($exifCoord) > 0 ? gps2Num($exifCoord[0]) : 0;
        $minutes = count($exifCoord) > 1 ? gps2Num($exifCoord[1]) : 0;
        $seconds = count($exifCoord) > 2 ? gps2Num($exifCoord[2]) : 0;

        $flip = ($hemi == 'W' or $hemi == 'S') ? -1 : 1;

        return $flip * ($degrees + $minutes / 60 + $seconds / 3600);
    }

    function gps2Num(string $coordPart): float
    {
        $parts = explode('/', $coordPart);

        if (count($parts) <= 0)
            return 0;

        if (count($parts) == 1)
            return $parts[0];

        return floatval($parts[0]) / floatval($parts[1]);
    }
    ?>

    <nav id="mainNav" class="navbar navbar-default navbar-fixed-top">
        <a href="gallery.php" class="link-to-gallery nav_cloudberry"></a>
        <a class="nav_info" href="info.php"></a>
        <div class="title">
            <a href="index.php?fn=<?= $prev_filename ?>" <?= (!isset($prev_filename) || empty($prev_filename) || $prev_filename == "") ? 'style="visibility: hidden;"' : '' ?>>
                <div class="arrow left"></div>
            </a>
            <a href="index.php">pinchards.is</a>
            <a href="index.php?fn=<?= $next_filename ?>" <?= (!isset($next_filename) || empty($next_filename) || $next_filename == "") ? 'style="visibility: hidden;"' : '' ?>>
                <div class="arrow right"></div>
            </a>
        </div>
    </nav>
    <div class="preview">
        <div class="placeholder" data-large="<?= htmlspecialchars($cfg['cdn_url_full'] . $filename, ENT_QUOTES, 'UTF-8') ?>" id="preview_image">
            <img src="images/photo/thumbnail.jpg" class="img-small">
            <div style="padding-bottom: 66.6%;"></div>
        </div>

        <div class="detail_view">
            <div class="btn_arrow"></div>
            <div class="col-md-5 container detail_container">
                <div class="detail_content_view">
                    <div>
                        <div class="detail_rect title_rect"><img src="images/icon-number.svg" /></div>
                        <div class="title">
                            <?php
                            $filename_array = explode(".", $filename);
                            $splited_file_name = "";
                            for ($i = 0; $i < count($filename_array) - 1; $i++) {
                                $splited_file_name .= $filename_array[$i];
                            }
                            $final_title = explode("GOPR", $splited_file_name);

                            echo $final_title[1];
                            ?>
                        </div>
                    </div>
                    <div class="datetime_area">
                        <div class="detail_rect"><img src="images/icon-date.svg" /></div>
                        <div class="inner_data">
                            <?php
                            $datetime = DateTime::createFromFormat('Y/m/d H:i:s', $datetime);
                            $converted_date = date_format($datetime, "l, F jS, Y @ g:i A");
                            echo $converted_date;
                            ?>
                        </div>
                    </div>
                    <div class="inner_area">
                        <div class="detail_rect"><img src="images/icon-gopro.svg" /></div>
                        <div class="inner_data">
                            <?php
                            if ($make) {
                                echo "Make: " . $make . "<br>";
                            } else {
                                echo "Make: <br>";
                            }

                            if ($model) {
                                echo "Model: " . $model . "<br>";
                            } else {
                                echo "Model: <br>";
                            }

                            if ($focal_length != '') {
                                $focal_length_array = explode("/", $focal_length);
                                $focal_value = number_format($focal_length_array[0] / $focal_length_array[1], 2);
                                echo "Focal Length: " . $focal_value . " mm<br>";
                            } else {
                                echo "Focal Length: <br>";
                            }

                            if ($exposure_time != '' && $fnumber != '' && $iso_speed_ratings != '') {
                                $exposure_array = explode("/", $exposure_time);
                                $exposure_value = number_format($exposure_array[1] / $exposure_array[0], 0);

                                $fnumber_array = explode("/", $fnumber);
                                $fnumber_value = number_format($fnumber_array[0] / $fnumber_array[1], 1);
                                echo "Exposure: " . "1/" . $exposure_value . " sec, " . "f/" . $fnumber_value . "; ISO " . $iso_speed_ratings . "<br>";
                            } else {
                                echo "Exposure: <br>";
                            }

                            if ($image_width != '' && $image_height != '') {
                                echo "Image Size: " . $image_width . " x " . $image_height . "<br>";
                            } else {
                                echo "Image Size: <br>";
                            }

                            if ($xresolution != '' && $yresolution != '') {
                                $resolution_array = explode("/", $xresolution);
                                $resolution_value = number_format($resolution_array[0] / $resolution_array[1], 2);
                                echo "Resolution: " . $resolution_value . " Pixel per Inch" . "<br>";
                            } else {
                                echo "Resolution: <br>";
                            }
                            ?>
                        </div>
                    </div>
                    <div class="inner_area">
                        <div class="detail_rect"><img src="images/icon-raspberry.svg" /></div>
                        <div class="inner_data">
                            <?php
                            echo "Photographer: Raspberry Pi 3 Model B";
                            /* if ($software != '') {
                            echo "Photographer: " . $software;
                        } else {
                            echo "Photographer: ";
                        } */
                            ?>
                        </div>
                    </div>
                    <div class="inner_area">
                        <div class="detail_rect"><img src="images/icon-geolocation.svg" /></div>
                        <div class="inner_data">
                            <?php
                            if (
                                $gps_latitude_degree != '' && $gps_latitude_min != '' && $gps_latitude_sec != '' &&
                                $gps_longitude_degree != '' && $gps_longitude_min != '' && $gps_longitude_sec != ''
                            ) {
                                echo "Position: " . $gps_latitude_degree . "&deg; " . $gps_latitude_min . "&acute; " . $gps_latitude_sec . "&quot; N, " .
                                    $gps_longitude_degree . "&deg; " . $gps_longitude_min . "&acute; " . $gps_longitude_sec . "&quot; W<br>";
                            } else {
                                // echo "Position: <br>";
                                $gps_latitude_degree = "49";
                                $gps_latitude_min = "12";
                                $gps_latitude_sec = "9.14";
                                $gps_longitude_degree = "53";
                                $gps_longitude_min = "29";
                                $gps_longitude_sec = "9.11";
                                echo "Position: " . $gps_latitude_degree . "&deg; " . $gps_latitude_min . "&acute; " . $gps_latitude_sec . "&quot; N, " .
                                    $gps_longitude_degree . "&deg; " . $gps_longitude_min . "&acute; " . $gps_longitude_sec . "&quot; W<br>";
                            }
                            if (empty($gps_altitude)) {
                                $gps_altitude = "5.27/1";
                            }

                            if ($gps_altitude != '') {
                                $alt_array = explode("/", $gps_altitude);
                                $alt_value = number_format($alt_array[0] / $alt_array[1], 2);
                                echo "Altitude: " . $alt_value . " m";
                            } else {
                                echo "Altitude: ";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-7 container mapcontainer">
                <div id="googleMap"></div>
            </div>

        </div>
    </div>



    <script>
        window.onload = function() {

            var placeholder = document.querySelector('.placeholder'),
                small = placeholder.querySelector('.img-small')

            // 1: load small image and show it
            var img = new Image();
            img.src = small.src;
            img.onload = function() {
                small.classList.add('loaded');
            };

            // 2: load large image
            var imgLarge = new Image();
            imgLarge.src = placeholder.dataset.large;
            imgLarge.onload = function() {
                imgLarge.classList.add('loaded');
            };
            placeholder.appendChild(imgLarge);
        }

        function myMap() {
            var lat = "<?php echo $lat; ?>";
            var lon = "<?php echo $lon; ?>";

            if (!lat || !lon) {
                lat = 49.2025694;
                lon = -53.48586388888953;
            }

            var myCenter = new google.maps.LatLng(lat, lon);
            var mapCanvas = document.getElementById("googleMap");
            var mapOptions = {
                center: myCenter,
                zoom: 14
            };
            var map = new google.maps.Map(mapCanvas, mapOptions);
            var marker = new google.maps.Marker({
                position: myCenter
            });
            marker.setMap(map);
        }
    </script>

    <!-- jQuery -->
    <script src="vendor/jquery/jquery.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="vendor/bootstrap/js/bootstrap.js"></script>

    <!-- Plugin JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.js"></script>
    <script src="vendor/scrollreveal/scrollreveal.js"></script>
    <script src="vendor/magnific-popup/jquery.magnific-popup.js"></script>

    <!-- Theme JavaScript -->
    <script src="js/pinchard.js"></script>

    <?php
    $pinchardGoogleMapsKey = pinchard_env_non_empty('GOOGLE_MAPS_API_KEY');
    if ($pinchardGoogleMapsKey !== null) {
        echo '<script src="https://maps.googleapis.com/maps/api/js?key=' . htmlspecialchars($pinchardGoogleMapsKey, ENT_QUOTES, 'UTF-8') . '&callback=myMap"></script>';
    }
    ?>

    <script>
        $(document).ready(function() {
            var diff = 0;
            if ($(".detail_view").height() - $(".btn_arrow").height() > $(document).height() / 3 * 2) {
                diff = $(document).height() / 3 * 2;
                $('.preview').addClass("overflow_shown");
            } else {
                diff = $(".detail_view").height() - $(".btn_arrow").height();
                $('.preview').removeClass("overflow_shown");
            }

            $('.btn_arrow').on("click", function() {
                if ($('.detail_view').hasClass("open")) {
                    $('.detail_view').animate({
                        marginTop: "+=" + diff
                    }, 500, "easeInOutCubic", function() {
                        $(".detail_view").removeClass('open');
                        $(".btn_arrow").removeClass("down_arrow");
                    });
                } else {
                    $('.detail_view').animate({
                        marginTop: "-=" + diff
                    }, 500, "easeInOutCubic", function() {
                        $(".detail_view").addClass('open');
                        $(".btn_arrow").addClass("down_arrow");
                    });
                }
            });
        });
    </script>

    <!-- Google Analytics -->
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-G1XKSQNT5M"></script>
    <script>
         window.dataLayer = window.dataLayer || [];
         function gtag(){dataLayer.push(arguments);}
         gtag('js', new Date());

         gtag('config', 'G-G1XKSQNT5M');
    </script>

</body>

</html>
