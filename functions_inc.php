<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);


require 'vendor/aws/vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

$s3 = new S3Client([
	'region' => 'us-east-1',
	'version' => '2006-03-01',
	'credentials' => [
		'key' => 'REDACTED_AWS_ACCESS_KEY_ID',
		'secret' => 'REDACTED_AWS_SECRET_ACCESS_KEY',
	],
]);

function getObjectList($bucket)
{
	global $s3;
	$array = [];
	$supported_image = ['gif', 'jpg', 'jpeg', 'png'];

	$objects = $s3->listObjectsV2([
		'Bucket' => $bucket,
	]);

	foreach ($objects['Contents'] as $object) {
		$key = $object['Key'];
		if ($key) {
			$ext = strtolower(pathinfo($key, PATHINFO_EXTENSION));
			if (in_array($ext, $supported_image)) {
				$dateString = $key;
				$dateString = explode("_", $dateString)[0];
				// Remove any folder-name and front slash
				if (strrpos($dateString, "/")) {
					$dateString = substr($dateString, strrpos($dateString, "/") + 1);
				}

				$date = DateTime::createFromFormat('Y-m-d\TH:i:s.000\Z', $dateString);
				$date = $date->format('Y/m/d H:i:s');
				$array[] = [
					"filename" => $key,
					"date" => $date,
				];
			}
		}
	}
	return $array;
}
