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
    
</head>

<body id="page-top">
    <?php        
        
        if (isset($_GET['fn']) && !empty($_GET['fn'])) {
            $filename = $_GET['fn'];
        }
        
        include 'getphoto.php';
    ?>
    
    <nav id="mainNav" class="navbar navbar-default navbar-fixed-top">
        <a href="gallery.php" class = "link-to-gallery">
            <div class = "union">
                <div class = "union_vector union_vector1"></div>
                <div class = "union_vector union_vector2"></div>
                <div class = "union_vector union_vector3"></div>
                <div class = "union_vector union_vector4"></div>
                <div class = "union_vector union_vector5"></div>
                <div class = "union_vector union_vector6"></div>
                <div class = "union_vector union_vector7"></div>
                <div class = "union_vector union_vector8"></div>
                <div class = "union_vector union_vector9"></div>
            </div>
        </a>
        <div class = "nav_info">
            <a href="info.php">i</a>
        </div>
        <div class = "title">
            <a href="index.php?fn=<?php echo $prev_filename; ?>" 
                <?php
                    if($prev_filename == "")
                        echo 'style = "visibility: hidden;"'; 
                ?>
            >
                <div class = "arrow left"></div>
            </a>            
            <a href="index.php">pinchards.is</a>            
            <a href="index.php?fn=<?php echo $next_filename; ?>" 
                <?php
                    if($next_filename == "")
                        echo 'style = "visibility: hidden;"'; 
                ?>
            >
                <div class = "arrow right"></div>
            </a>            
        </div>
    </nav>
    <div class = "preview">
        <img src="<?php echo "photo/" . $filename; ?>" id="preview_image"/>
        <div class = "detail_view">
            <div class ="btn_arrow"></div>
            <div class = "col-md-5 container detail_container">
                <div class = "detail_content_view">
                    <div>
                        <div class = "detail_rect title_rect"></div>
                        <div class = "title">
                            <?php
                                $filename_array = explode(".", $filename);
                                $splited_file_name = "";                                
                                for( $i = 0; $i < count($filename_array)-1; $i++ ) {
                                    $splited_file_name .= $filename_array[$i];
                                }
                                echo $splited_file_name;
                            ?>
                        </div>
                    </div>
                    <div class = "datetime_area">
                        <div class = "detail_rect"></div>
                        <div class = "inner_data">
                            <?php                            
                                $datetime = DateTime::createFromFormat('Y:m:d H:i:s', $datetime);
                                $converted_date = date_format($datetime, "l, F jS, Y @ g:i A");
                                echo $converted_date;
                            ?>
                        </div>
                    </div>
                    <div class = "inner_area">
                        <div class = "detail_rect"></div>
                        <div class = "inner_data">
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
                                
                                if($focal_length != '') {
                                    $focal_length_array = explode("/", $focal_length);
                                    $focal_value = number_format($focal_length_array[0] / $focal_length_array[1], 2);
                                    echo "Focal Length: " . $focal_value . " mm<br>";
                                } else {
                                    echo "Focal Length: <br>";
                                }
                                
                                if($exposure_time != '' && $fnumber != '' && $iso_speed_ratings != '') {
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
                                
//                                if ($str_flash != '') {
//                                    echo "Flash: " . $str_flash . "<br>";
//                                } else {
//                                    echo "Flash: <br>";
//                                }                                
                            ?>
                        </div>
                    </div>
                    <div class = "inner_area">
                        <div class = "detail_rect"></div>
                        <div class = "inner_data">
                            <?php
                                if ($software != '') {
                                    echo "Photographer: " . $software;
                                } else {
                                    echo "Photographer: ";
                                }                                
                            ?>
                        </div>
                    </div>
                    <div class = "inner_area">
                        <div class = "detail_rect"></div>
                        <div class = "inner_data">
                            <?php 
                                if ($gps_latitude_degree != '' && $gps_latitude_min != '' && $gps_latitude_sec != '' && 
                                        $gps_longitude_degree != '' && $gps_longitude_min != '' && $gps_longitude_sec != '') {                                    
                                    echo "Position: " . $gps_latitude_degree . "&deg; " . $gps_latitude_min . "&acute; " . $gps_latitude_sec . "&quot; N, " . 
                                                    $gps_longitude_degree . "&deg; " . $gps_longitude_min . "&acute; " . $gps_longitude_sec . "&quot; W<br>";
                                } else {
//                                    echo "Position: <br>";
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
            <div class = "col-md-7 container mapcontainer">
                <div id="googleMap"></div>
            </div>
            
        </div>
    </div>
    
<script>
    function myMap() {
        var lat =  "<?php echo $lat; ?>";
        var lon = "<?php echo $lon; ?>";
        
        if (!lat || !lon) {
            lat = 49.2025694;
            lon = -53.48586388888953;
        }

        var myCenter = new google.maps.LatLng(lat,lon);
        var mapCanvas = document.getElementById("googleMap");
        var mapOptions = {center: myCenter, zoom: 14};
        var map = new google.maps.Map(mapCanvas, mapOptions);
        var marker = new google.maps.Marker({position:myCenter});
        marker.setMap(map);
    }
</script>
    

    <!-- jQuery -->
    <script src="vendor/jquery/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Plugin JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js"></script>
    <script src="vendor/scrollreveal/scrollreveal.min.js"></script>
    <script src="vendor/magnific-popup/jquery.magnific-popup.min.js"></script>

    <!-- Theme JavaScript -->
    <script src="js/pinchard.min.js"></script>
    
    <script src="https://maps.googleapis.com/maps/api/js?key=REDACTED_GOOGLE_MAPS_KEY&callback=myMap"></script>    

</body>

</html>

<script>    
    $(document).ready(function() {
        
        var diff = 0;
        if ($(".detail_view").height() - $(".btn_arrow").height() > $(document).height() / 3 * 2) {
            diff = $(document).height() / 3 * 2;            
            $('.preview').addClass("overflow_shown");
        }
        else  {
            diff = $(".detail_view").height() - $(".btn_arrow").height();            
            $('.preview').removeClass("overflow_shown");
        }
                
        $('.btn_arrow').on("click", function(){
            if($('.detail_view').hasClass("open")) {                
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