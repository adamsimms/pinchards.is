<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


require 'vendor/aws/aws/aws-autoloader.php';
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

$s3 = S3Client::factory(array(
	'credentials' => array(
		'key'    => 'KEY',
		'secret' => 'SECRET'
		),
	'region' => 'us-east-1',
	'version'=>'2006-03-01'
	));

function getObjectList($bucket){
	global $s3;
	//$bucket = 'shutter-island';
	$array =array();
	$supported_image = array(
		'gif',
		'jpg',
		'jpeg',
		'png'
		);
	$objects = $s3->getIterator('ListObjects', array(
		"Bucket" => $bucket,
    	//"Prefix" => ''
		));

	foreach ($objects as $object) {
		$key = $object['Key'];
		if ($key) {
			$ext = strtolower(pathinfo($key, PATHINFO_EXTENSION));
			if (in_array($ext, $supported_image)) {
				$dateString = $key;
				$dateString = explode("_", $dateString)[0];
                // Remove any folder-name and front slash
				if(strrpos($dateString,"/")){
					$dateString = substr($dateString, strrpos($dateString,"/")+1);  
				}

                $date = DateTime::createFromFormat('Y-m-d\TH:i:s.000\Z', $dateString);//$content->LastModified
                $date = date_format($date,"Y/m/d H:i:s");
                $array[] = array(
                	"filename"=>$key,
                	"date"=>$date,
                	);
            }
        }
    }
    return $array;
}

?>
