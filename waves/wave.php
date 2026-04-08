<?php
date_default_timezone_set("UTC");

$currentDate =  date("Y-m-d\TH:i:s\Z");
//echo $currentDate . '<br />';

$time = strtotime($currentDate) - (3* 24*60 * 60);
$starttDate = date("Y-m-d\TH:i:s\Z", $time);

//echo $starttDate . '<br />';

$url = "https://www.smartatlantic.ca/erddap/tabledap/SMA_st_johns.json?station_name,time,longitude,latitude,wind_spd_avg,wind_spd_max,wind_dir_avg,air_temp_avg,air_pressure_avg,air_humidity_avg,air_dewpoint_avg,surface_temp_avg,wave_ht_max,wave_ht_sig,wave_period_max,wave_dir_avg,wave_spread_avg,curr_dir_avg,curr_spd_avg&time>=" . $starttDate . "&time<=" . $currentDate;

//echo $url;
$result = file_get_contents($url);
$data = (json_decode($result, true));
//print_r($data);
//print_r($data['table']['rows'][0]);
//echo $data;
$sizeRows = sizeof($data['table']['rows']);
$wdata = $data['table']['rows'][$sizeRows - 1];
//print_r($wdata);
$wind = $wdata[4];
$size = (100 + $wdata[14]); // wave_period_max
$choppiness = $wdata[12]; // wave_ht_max
//echo '<br /> WIND=> '.$wind;
//echo '<br /> size=> '.$size;
//echo '<br /> choppiness=> '.$choppiness;


// exit;
// $size = isset($_GET['size']) ? $_GET['size'] : 100;
// $choppiness = isset($_GET['choppiness']) ? $_GET['choppiness'] : 1.1;
// $wind = isset($_GET['wind']) ? $_GET['wind'] : 10;
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Ocean Wave Simulation</title>

    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700,800' rel='stylesheet' type='text/css'>
    <link href="waves.css" rel="stylesheet" type="text/css">
    <style>
        .stationName {
            color: #333333;
            font-size: 22px;
            font-family: 'Open Sans', sans-serif;
            width: 300px;
            margin: 0 auto;
        }
        .stationName div,span{
            font-size: 22px !important;
        }
        .stationName #choppiness{
            position: inherit;
        }
    </style>
</head>

<body>
    <div id="overlay"></div>
    <div id="ui">
        

        <div class="stationName">
            <div><?php echo $wdata[0]; ?></div>
            <div id="datetime"> <?php echo date("Y-m-d H:i:s ", strtotime($wdata[1])); ?></div>
            <div>WIND <span id="wind-speed"></span><span id="wind-unit"> m/s</span></div>
            <div>SIZE <span id="size-value"></span><span id="size-unit"> m</span></div>
            <div>CHOPPINESS <span id="choppiness"></span></div>
        </div>

        <div id="camera" style="display: none;">
            <canvas id="profile" width="350" height="105"></canvas>
            <div id="length"></div>
            <div id="end"></div>
            <div id="wind">
                <span id="wind-speed"></span><span id="wind-unit"> m/s</span>
                <div id="wind-label">WIND</div>
            </div>

            <div id="size">
                <span id="size-value"></span><span id="size-unit"> m</span>
            </div>
            <div id="size-label">SIZE</div>
            <div id="choppiness"></div>
            <div id="choppiness-label">CHOPPINESS</div>
        </div>

    </div>
   <span style="position: relative; display:block"> <canvas id="simulator"></canvas></span>

    <div id="error">Your browser does not appear to support the required technologies.</div>
    <script src="jquery.min.js"></script>


    <script>
        var INITIAL_SIZE = <?php echo $size; ?>,
            INITIAL_WIND = [<?php echo $wind; ?>, <?php echo $wind; ?>],
            INITIAL_CHOPPINESS = <?php echo $choppiness; ?>;
    </script>


    <script src="shared.js"></script>
    <script src="simulation.js"></script>
    <script src="ui.js"></script>
    <script src="waves.js"></script>

    <script>
        $(document).ready(function() {

            $("#simulator").width(window.width*3.2);
            $("#simulator").css("top","400px");
            $("#simulator").height(window.height);
            $("#simulator").css("left","-120%");


            function getLatestData(){
                console.log('updating new values');
                $.ajax({
                type: "GET",
                url: "call-api.php",
                success: function(json_data){
                    var resp = $.parseJSON(json_data);
                    $("#choppiness").text(resp.choppiness);
                    $("#wind-speed").text(resp.wind);
                    $("#size-value").text(resp.size);
                    $("#datetime").text(resp.time);
                    console.log(resp);
                }
                });
            }
            
            setInterval(function () {
                getLatestData();
            },10*1000);

        });
    </script>

</body>

</html>