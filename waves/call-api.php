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
$output = array();
$output['wind'] = $wind;
$output['size'] = $size;
$output['choppiness'] = $choppiness;
$output['time'] = date("Y-m-d H:i:s ", strtotime($wdata[1]));

echo json_encode($output);
// exit;
// $size = isset($_GET['size']) ? $_GET['size'] : 100;
// $choppiness = isset($_GET['choppiness']) ? $_GET['choppiness'] : 1.1;
// $wind = isset($_GET['wind']) ? $_GET['wind'] : 10;
?>