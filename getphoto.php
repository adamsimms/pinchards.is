<?php

$files_iter = glob("photo/*.*");

    $total_array = array();
    $currentFileIndex = -1;

    $iter_index = 0;

    foreach ($files_iter as $file_iter) {

        //$exif = exif_read_data($file, 'IFD0');
        //echo $exif===false ? "No header data found.<br />\n" : "Image contains headers<br />\n";

        $exif_iter = exif_read_data($file_iter, 0, true);

        $filename_iter = "";
        if (isset($exif_iter['FILE']['FileName'])) {
            $filename_iter = $exif_iter['FILE']['FileName'];
        }

        $datetime_iter = "";

        if (isset($exif_iter['EXIF']['DateTimeOriginal'])) {
            $datetime_iter = $exif_iter['EXIF']['DateTimeOriginal'];
        } else if (isset($exif_iter['EXIF']['DateTimeDigitized'])) {
            $datetime_iter = $exif_iter['EXIF']['DateTimeDigitized'];
        } else if (isset($exif_iter['IFD0']['DateTime'])) {
            $datetime_iter = $exif_iter['IFD0']['DateTime'];
        } else if (isset($exif_iter['FILE']['FileDateTime'])) {
            $datetime_iter = date("Y:m:d H:i:s", $exif_iter['FILE']['FileDateTime']);
        }

        $total_array[] = array(
                    "filename"=>$filename_iter,
                    "datetime"=>$datetime_iter,
                    );

        $iter_index++;
    }

    usort($total_array, function($a, $b) {
        return $a['datetime'] > $b['datetime'];
    });

    $prev_filename = "";
    $next_filename = "";

    if ( !empty($filename) ) {
        for( $i = 0 ; $i < count($total_array) ; $i++ )  {
            if ( $total_array[$i]['filename'] == $filename) {
                if ($i != 0) {
                    $prev_filename = $total_array[$i-1]['filename'];
                }
                if ($i != count($total_array)-1) {
                    $next_filename = $total_array[$i+1]['filename'];
                }
            }
        }
    } else {//get latest filename

        if (count($total_array) > 0) {
            $filename = $total_array[count($total_array)-1]['filename'];
            if (count($total_array) > 1) {
                $prev_filename = $total_array[count($total_array)-2]['filename'];
            }
        }
    }

    $array = array();
    $file = "photo/" . $filename;

    $exif = exif_read_data($file, 0, true);

    if (isset($exif['FILE']['FileName'])) {
        $filename = $exif['FILE']['FileName'];
    }

    $datetime = "";
    if (isset($exif['EXIF']['DateTimeOriginal'])) {
        $datetime = $exif['EXIF']['DateTimeOriginal'];
    } else if (isset($exif['EXIF']['DateTimeDigitized'])) {
        $datetime = $exif['EXIF']['DateTimeDigitized'];
    } else if (isset($exif['IFD0']['DateTime'])) {
        $datetime = $exif['IFD0']['DateTime'];
    } else if (isset($exif['FILE']['FileDateTime'])) {
        $datetime = date("Y:m:d H:i:s", $exif['FILE']['FileDateTime']);
    }

    $make = "";
    if (isset($exif['IFD0']['Make'])) {
        $make = $exif['IFD0']['Make'];
    }

    $model = "";
    if (isset($exif['IFD0']['Model'])) {
        $model = $exif['IFD0']['Model'];
    }

    $focal_length = "";
    if (isset($exif['EXIF']['FocalLength'])) {
        $focal_length = $exif['EXIF']['FocalLength'];
    }

    $exposure_time = "";
    if (isset($exif['EXIF']['ExposureTime'])) {
        $exposure_time = $exif['EXIF']['ExposureTime'];
    }

    $fnumber = "";
    if (isset($exif['EXIF']['FNumber'])) {
        $fnumber = $exif['EXIF']['FNumber'];
    }

    $iso_speed_ratings = "";
    if (isset($exif['EXIF']['ISOSpeedRatings'])) {
        $iso_speed_ratings = $exif['EXIF']['ISOSpeedRatings'];
    }

    $image_width = "";
    $image_height = "";
    if (isset($exif['COMPUTED']['Width'])) {
        $image_width = $exif['COMPUTED']['Width'];
    } else if (isset($exif['EXIF']['ExifImageWidth'])) {
        $image_width = $exif['EXIF']['ExifImageWidth'];
    }
    if (isset($exif['IFD0']['Height'])) {
        $image_height = $exif['IFD0']['Height'];
    } else if (isset($exif['EXIF']['ExifImageLength'])) {
        $image_height = $exif['EXIF']['ExifImageLength'];
    }

    $xresolution = "";
    $yresolution = "";
    if (isset($exif['IFD0']['XResolution'])) {
        $xresolution = $exif['IFD0']['XResolution'];
    } else if (isset($exif['THUMBNAIL']['XResolution'])) {
        $xresolution = $exif['THUMBNAIL']['XResolution'];
    }
    if (isset($exif['IFD0']['YResolution'])) {
        $yresolution = $exif['IFD0']['YResolution'];
    } else if (isset($exif['THUMBNAIL']['YResolution'])) {
        $yresolution = $exif['THUMBNAIL']['YResolution'];
    }

    $flash = "";
    $str_flash = "";
    if (isset($exif['EXIF']['Flash'])) {
        $flash = $exif['EXIF']['Flash'];
    }

    if (($flash & 0x0) != 0) {
        $str_flash = "No Flash";
    } else if (($flash & 0x1) != 0) {
        $str_flash = "Fired";
    } else if (($flash & 0x5) != 0) {
        $str_flash = "Fired, Return not detected";
    } else if (($flash & 0x7) != 0) {
        $str_flash = "Fired, Return detected";
    } else if (($flash & 0x8) != 0) {
        $str_flash = "On, Did not fire";
    } else if (($flash & 0x9) != 0) {
        $str_flash = "On, Fired";
    } else if (($flash & 0xd) != 0) {
        $str_flash = "On, Return not detected";
    } else if (($flash & 0xf) != 0) {
        $str_flash = "On, Return detected";
    } else if (($flash & 0x10) != 0) {
        $str_flash = "Off, Did not fire";
    } else if (($flash & 0x14) != 0) {
        $str_flash = "Off, Did not fire, Return not detected";
    } else if (($flash & 0x18) != 0) {
        $str_flash = "Auto, Did not fire";
    } else if (($flash & 0x19) != 0) {
        $str_flash = "Auto, Fired";
    } else if (($flash & 0x1d) != 0) {
        $str_flash = "Auto, Fired, Return not detected";
    } else if (($flash & 0x1f) != 0) {
        $str_flash = "Auto, Fired, Return detected";
    } else if (($flash & 0x20) != 0) {
        $str_flash = "No flash function";
    } else if (($flash & 0x30) != 0) {
        $str_flash = "Off, No flash function";
    } else if (($flash & 0x41) != 0) {
        $str_flash = "Fired, Red-eye reduction";
    } else if (($flash & 0x45) != 0) {
        $str_flash = "Fired, Red-eye reduction, Return not detected";
    } else if (($flash & 0x47) != 0) {
        $str_flash = "Fired, Red-eye reduction, Return detected";
    } else if (($flash & 0x49) != 0) {
        $str_flash = "On, Red-eye reduction";
    } else if (($flash & 0x4d) != 0) {
        $str_flash = "On, Red-eye reduction, Return not detected";
    } else if (($flash & 0x4f) != 0) {
        $str_flash = "On, Red-eye reduction, Return detected";
    } else if (($flash & 0x50) != 0) {
        $str_flash = "Off, Red-eye reduction";
    } else if (($flash & 0x58) != 0) {
        $str_flash = "Auto, Did not fire, Red-eye reduction";
    } else if (($flash & 0x59) != 0) {
        $str_flash = "Auto, Fired, Red-eye reduction";
    } else if (($flash & 0x5d) != 0) {
        $str_flash = "Auto, Fired, Red-eye reduction, Return not detected";
    } else if (($flash & 0x5f) != 0) {
        $str_flash = "Auto, Fired, Red-eye reduction, Return detected";
    }

    $software = "";
    if (isset($exif['IFD0']['Software'])) {
        $software = $exif['IFD0']['Software'];
    }

    $gps_latitude_degree = $gps_latitude_min = $gps_latitude_sec = "";
    $gps_longitude_degree = $gps_longitude_min = $gps_longitude_sec = "";

    $gps_latitude_ref = "";
    $gps_longitude_ref = "";
    $gps_altitude = "";
    $lon = "";
    $lat = "";

    if (isset($exif['GPS']['GPSLatitude'])) {
        $gps_latitude_array = $exif['GPS']['GPSLatitude'];
        $gps_latitude_degree = explode('/',$gps_latitude_array[0])[0];
        $gps_latitude_min = explode('/',$gps_latitude_array[1])[0];
        $gps_latitude_sec = number_format(explode('/',$gps_latitude_array[2])[0] / explode('/',$gps_latitude_array[2])[1], 2);
    }
    if (isset($exif['GPS']['GPSLongitude'])) {
        $gps_longitude_array = $exif['GPS']['GPSLongitude'];
        $gps_longitude_degree = explode('/',$gps_longitude_array[0])[0];
        $gps_longitude_min = explode('/',$gps_longitude_array[1])[0];
        $gps_longitude_sec = number_format(explode('/',$gps_longitude_array[2])[0] / explode('/',$gps_longitude_array[2])[1], 2);
    }
    if (isset($exif['GPS']['GPSAltitude'])) {
        $gps_altitude = $exif['GPS']['GPSAltitude'];
    }
    if (isset($exif['GPS']['GPSLatitudeRef'])) {
        $gps_longitude_ref = $exif['GPS']['GPSLatitudeRef'];
    }
    if (isset($exif['GPS']['GPSLongitudeRef'])) {
        $gps_longitude_ref = $exif['GPS']['GPSLongitudeRef'];
    }

    if (isset($exif['GPS']['GPSLatitude']) && isset($exif['GPS']['GPSLongitude'])) {
        $lon = getGps($exif['GPS']["GPSLongitude"], $exif['GPS']['GPSLongitudeRef']);
        $lat = getGps($exif['GPS']["GPSLatitude"], $exif['GPS']['GPSLatitudeRef']);
    }

function getGps($exifCoord, $hemi) {

    $degrees = count($exifCoord) > 0 ? gps2Num($exifCoord[0]) : 0;
    $minutes = count($exifCoord) > 1 ? gps2Num($exifCoord[1]) : 0;
    $seconds = count($exifCoord) > 2 ? gps2Num($exifCoord[2]) : 0;

    $flip = ($hemi == 'W' or $hemi == 'S') ? -1 : 1;

    return $flip * ($degrees + $minutes / 60 + $seconds / 3600);

}

function gps2Num($coordPart) {

    $parts = explode('/', $coordPart);

    if (count($parts) <= 0)
        return 0;

    if (count($parts) == 1)
        return $parts[0];

    return floatval($parts[0]) / floatval($parts[1]);
}

?>
