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

</head>

<!--<body id="page-top" onload="myFunction()" >-->
<body id="page-top">
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
            <a href="index.php">pinchards.is</a>
        </div>
    </nav>
    <div class = "picker_area">            
            <div id="monthpicker"></div>
    </div>
    <!--<div id="loader"></div>-->
    <!--<div class = "content_area animate-bottom">-->
    <div class = "content_area">
        <div class = "container" id ='photo_container'>
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
</body>

</html>

<?php 
    if (isset($_GET['cury']) && !empty($_GET['cury']) && isset($_GET['curm']) && !empty($_GET['curm']) ) {
        $current_year = $_GET['cury'];
        $current_month = $_GET['curm'];        
    } else {
        $current_month = date('m');
        $current_year = date('Y');
//        echo "phpcurr_month = " . $current_month;
    }
    ini_set('allow_url_fopen', 'on');
    $cdnurl = 'http://d3kq73uimqeic8.cloudfront.net/';
//    $xml = simplexml_load_file($cdnurl) or die("Error: Cannot create object");
    $ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $cdnurl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$xmlresponse = curl_exec($ch);
$xml=simplexml_load_string($xmlresponse);



    $array = array();
    $supported_image = array(
    'gif',
    'jpg',
    'jpeg',
    'png'
    );
    $validYearArray = array();
    $validMonthArray = array();

    foreach($xml->children() as $content) {
        if ($content->Key) {
            $ext = strtolower(pathinfo($content->Key, PATHINFO_EXTENSION));
            if (in_array($ext, $supported_image)) {             
                $dateString = $content->Key;                
                $dateString = explode("_", $dateString)[0];                
                $date = DateTime::createFromFormat('Y-m-d\TH:i:s.000\Z', $dateString);//$content->LastModified
                
//                $date = DateTime::createFromFormat('Y-m-d\TH:i:s.000\Z', $content->LastModified);     
                $formatted_date = date_format($date,"Y/m/d H:i:s");                
                $year = date_format($date, "Y");
                $month = date_format($date, "m");
                
                if(($month == $current_month) && ($year == $current_year)) {
                    $array[] = array(
                            "filename"=>$content->Key,
                            "date"=>$formatted_date,
                            );
                }
                
                $year_month = $year. "-" .$month;
                if (!in_array($year_month, $validMonthArray)) {
                    $validMonthArray[] = $year_month;
                }
            }
            
        }
    }
    usort($array, function($a, $b) {
        return $a['date'] > $b['date'];
    });
    usort($validMonthArray, function($a, $b) {
        $a_date = DateTime::createTimeFormat('Y-m', $a);
        $b_date = DateTime::createTimeFormat('Y-m', $b);
        return $a_date > $b_date;
    });
    
    $prev_month = "";
    $next_month = "";
    $curr_index = array_search($current_month . "-" . $current_year, $validMonthArray);
    if ($curr_index > 0) {
        $prev_month = $validMonthArray[$curr_index - 1];
    }
    if ($curr_index < sizeof($validMonthArray)-1) {
        $next_month = $validMonthArray[$curr_index + 1];
    }
?>

<script>
    window.addEventListener('load', function(){
    var allimages= document.getElementsByTagName('img');
    for (var i=0; i<allimages.length; i++) {
        if (allimages[i].getAttribute('data-src')) {
            allimages[i].setAttribute('src', allimages[i].getAttribute('data-src'));
        }
    }
}, false);
    
    
    var array = <?php echo json_encode($array) ?>;
    
    $('#photo_container').empty();
    var prev_day = "0";
    var html = "";
    var row_num = 0;
    var selectedDate = 0;
    var cdnurl = 'http://d3kq73uimqeic8.cloudfront.net/';
    
    for( var index = 0 ; index < array.length ; index++ ) {

        var photo = array[index];
        var date = new Date(photo['date']);
        var day = date.getDate();
        
        if (day != prev_day) {
            if (prev_day != "") {//it's not first, add close divs
                html += '</div></div>';
            }
            prev_day = day;

            html += '<div class="row">';
                html += '<div class = "day">';
                    html += day;
                html += '</div>';
                html += '<div class = "photos" id="photos">';

                row_num++;
        }

                    html += '<div class="col-md-5ths col-sm-6 col-xs-12 photoElement">';
                        html += '<a href = "index.php?fn=' + photo['filename'][0] + '" class = "photoBox">';
                            html += '<img src = "photo/3.jpg" data-src="' + cdnurl + photo['filename'][0] + '" class="img-responsive" alt="" style = "width: 100%">';                            
                            html += '<div class="photo-box-caption">';
                                html += '<div class="photo-box-caption-content">';
                                    html += formatAMPM(date);
                                html += '</div>';
                            html += '</div>';
                        html += '</a>';
                    html += '</div>';
    }
    $('#photo_container').append(html);    
    
    function formatAMPM(date) {
        var hours = date.getHours();
        var minutes = date.getMinutes();
        var ampm = hours >= 12 ? 'pm' : 'am';
        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        minutes = minutes < 10 ? '0'+minutes : minutes;
        var strTime = hours + ':' + minutes + ' ' + ampm;
        
        return strTime;
    }
    
    function formatDate(date) {
        var time = formatAMPM(new Date(date));
            
        return time;
    }
    
    function parseDate(s) {
        var b = s.split(/\D/);
        return new Date(b[0],b[1]-1,b[2],b[3],b[4],b[5]);
    }
    var row_num = 0;
    function loadPhotos(date, valastmon) {
        return;
        
        $.ajax({
            url:'getphotos.php',
            data:{
                date:date,
                valastmon: valastmon
            },
            method:'post',
            success:function(result){
                $('.ui-datepicker-month').css('color', 'rgba(0, 0, 0, 0.8)');
                
                var data = JSON.parse(result);
                var array = data['array'];
                var validYearArray = data['validYearArray'];
                var validMonthArray = data['validMonthArray'];
                
                
                $(".ui-datepicker-month > option").each(function() {
                    
                    for (var i = 0; i < validMonthArray.length; i++) {
                        var month_value = pad(parseInt(this.value) + 1);
                        if(validMonthArray.indexOf(month_value) == -1) {
                            $(".ui-datepicker-month option[value='" + this.value + "']").remove();//prop('disabled', true);//remove();
                        }
                    }
                });
                $(".ui-datepicker-year > option").each(function() {
                    for (var i = 0; i < validYearArray.length; i++) {                        
                        var year_value = pad(parseInt(this.value) + 1);                        
                        if(validYearArray.indexOf(this.value) == -1) {
                            $(".ui-datepicker-year option[value='" + this.value + "']").remove(); 
                        }
                    }
                });
                
                $('#photo_container').empty();
                
                var prev_day = "0";
                var html = "";
                row_num = 0;
                for(var index = 0; index < array.length; index++)
                {
                    var photo = array[index];
                    
                    var day = ((photo["datetime"].split(" "))[0].split(":"))[2];
                    if (day != prev_day) {
                        if (prev_day != "") {//it's not first, add close divs
                            html += '</div></div>';
                        }
                        prev_day = day;
                        
                        html += '<div class="row">';
                            html += '<div class = "day">';
                                html += day;
                            html += '</div>';
                            html += '<div class = "photos" id="photos">';
                            
                            row_num++;
                    }
                    
                    var old_date = parseDate(photo['datetime']);
                    var new_date = formatDate(old_date);
                    
                
                                html += '<div class="col-md-5ths col-sm-6 col-xs-12 photoElement">';
                                    html += '<a href = "index.php?fn=' + photo['filename'] + '" class = "photoBox">';
                                        html += '<img src="photo/' + photo['filename'] + '" class="img-responsive" alt="">';
                                        html += '<div class="photo-box-caption">';
                                            html += '<div class="photo-box-caption-content">';
                                                html += new_date;
                                            html += '</div>';
                                        html += '</div>';
                                    html += '</a>';
                                html += '</div>';
                }
                $('#photo_container').append(html);
            }
        });  
    }
    
    function pad(d) {
        return (d < 10) ? '0' + d.toString() : d.toString();
    }
    var current_month = -1;
    var current_year = -1;
    
    
//    console.log("<?php echo "currdate = ". $current_year . $current_month; ?>");
    var defaultDate = new Date();
//    console.log("defaultDate1 = " + defaultDate);
    defaultDate.setMonth(<?php echo $current_month-1; ?>);
    defaultDate.setYear(<?php echo $current_year; ?>);
    
    
    
    
    $('#monthpicker').datepicker({
        dateFormat: "mm/yy",
        changeMonth: true,
        changeYear: true,
        inline: true,
        yearRange: "-20:+0",
        monthNamesShort: $.datepicker.regional["en"].monthNames,
        changeMonth: true,
        defaultDate: defaultDate
    }).on("input change", function (e) {

    });
    
    
    var temp_date = $("#monthpicker").datepicker('getDate');
//    console.log("temp" + temp_date);
    
    $('#monthpicker').datepicker('option', 'onChangeMonthYear', function(year, month) {            
            var url = window.location.href.split('?')[0] + '?cury=' + year + '&curm=' + month;
            window.location.href = url;
    });
    
    $( document ).ready(function() {
        
    var validYearArray = <?php echo json_encode($validYearArray );?>;
    var validMonthArray = <?php echo json_encode($validMonthArray );?>;
    
    
    $(".ui-datepicker-month > option").each(function() {

        for (var i = 0; i < validMonthArray.length; i++) {
            var month_value = pad(parseInt(this.value) + 1);            
            temp_date = <?php echo $current_year; ?> + "-" + month_value;
//            console.log("ttttempdate = " + temp_date);
            if(validMonthArray[i] != temp_date) {
                $(".ui-datepicker-month option[value='" + this.value + "']").remove();//prop('disabled', true);//remove();
            }
        }
    });
    $(".ui-datepicker-year > option").each(function() {
        for (var i = 0; i < validMonthArray.length; i++) {
            var year_value = pad(parseInt(this.value) ); 
//            console.log("year_value = " + year_value);
            if(validMonthArray[i].split("-")[0] != year_value) {
                $(".ui-datepicker-year option[value='" + this.value + "']").remove(); 
            }
        }
    });
    
    
    
    $( ".ui-datepicker-prev" ).click(function(e) {
//        console.log( "Handler for .click() called." );
        e.stopImmediatePropagation();
        e.stopPropagation();
        e.preventDefault();
        return false;
    });
    $( ".ui-datepicker-next" ).click(function(e) {
//        console.log( "Handler for .click() called." );
        e.stopImmediatePropagation();
        e.stopPropagation();
        e.preventDefault();
        return false;
    });
        
        
        $(window).scroll(function() {
            var offsetTop = $('.photos').offset().top;
            var scrollTop = $(window).scrollTop();
            
            if (scrollTop > 0) {
                var found = 0;
                
                for( var i = (row_num - 1) ; i >= 0 ; i-- ) {
                
                    var val = $('#photo_container .row:eq('+i+') .photos').position().top - 132 - 20;
                    
                    if (val < scrollTop && !found ) {
                        $('#photo_container .row:eq('+i+') .day').addClass('floating');
                        $('#photo_container .row:eq('+i+') .photos').addClass('floating');
                        found = 1;
                        
                    } else {
                        $('#photo_container .row:eq('+i+') .day').removeClass('floating');
                        $('#photo_container .row:eq('+i+') .photos').removeClass('floating');                        
                    }
                }
            }
        });
    });
</script>
