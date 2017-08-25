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

<body id="page-top" onload="myFunction()" >
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
            <a href="info.php"><img src="img/icon-info.svg" /></a>
        </div>
        <div class = "title">
            <a href="index.php">pinchards.is</a>
        </div>
    </nav>
    <div class = "picker_area">
            <div id="monthpicker"></div>
    </div>
    <div id="loader"></div>
    <div class = "content_area animate-bottom">
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


<script>
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

        $.ajax({
            url:'getphotos.php',
            data:{
                date:date,
                valastmon: valastmon
            },
            method:'post',
            success:function(result){
//                console.log(result);
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
                                        html += '<img src="photo/' + photo['filename'] + '" class="img-responsive" alt="" style = "width: 100%">';
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
    $('#monthpicker').datepicker({
        dateFormat: "mm/yy",
        changeMonth: true,
        changeYear: true,
        inline: true,
        yearRange: "-20:+0",
        monthNamesShort: $.datepicker.regional["en"].monthNames,
        changeMonth: true
    }).on("input change", function (e) {

            var yearval = $('.ui-datepicker-year').val();

            var month_index = $('.ui-datepicker-month').val();
            month_index++;
            var monthval = pad(month_index);



            if (e.target.className == "ui-datepicker-year") {
                loadPhotos(monthval + "-" + yearval, "1");
            } else {
                loadPhotos(monthval + "-" + yearval, "0");
            }
    });

    $('#monthpicker').datepicker('option', 'onChangeMonthYear', function(year, month) {
//        loadPhotos(month + "-" + year, "0");
    })

    $( document ).ready(function() {
        var date = $("#monthpicker").datepicker("getDate");
        var curdate = $.datepicker.formatDate("mm-yy", date);
        loadPhotos(curdate, "0");



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

<script>
    var myVar;

    function myFunction() {
        myVar = setTimeout(showPage, 3000);
    }

    function showPage() {
      $("#loader").hide();
      $('.content_area').show();
}
</script>
