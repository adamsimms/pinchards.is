<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

// DreamHost / local: copy aws-env.local.php.example → aws-env.local.php (gitignored) and set values.
$awsEnv = __DIR__ . '/aws-env.local.php';
if (is_readable($awsEnv)) {
	require $awsEnv;
}

require 'vendor/aws/vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

// Credentials: AWS_ACCESS_KEY_ID + AWS_SECRET_ACCESS_KEY via aws-env.local.php (putenv) or real env vars.
$s3 = new S3Client([
	'region' => getenv('AWS_DEFAULT_REGION') ?: 'us-east-1',
	'version' => '2006-03-01',
]);

function getObjectList($bucket)
{
	global $s3;
	$array = [];
	$supported_image = ['gif', 'jpg', 'jpeg', 'png'];

	try {
		$token = null;
		do {
			$params = ['Bucket' => $bucket];
			if ($token !== null) {
				$params['ContinuationToken'] = $token;
			}
			$objects = $s3->listObjectsV2($params);
			if (empty($objects['Contents'])) {
				break;
			}
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
			$token = !empty($objects['IsTruncated']) ? ($objects['NextContinuationToken'] ?? null) : null;
		} while ($token !== null);
	} catch (AwsException $e) {
		$code = $e->getAwsErrorCode() ?: $e->getCode();
		$msg = $e->getAwsErrorMessage() ?: $e->getMessage();
		throw new RuntimeException(
			'S3 access failed (' . $code . '): ' . $msg
			. ' — For ListBucket errors, fix IAM: the principal needs s3:ListBucket on the bucket and s3:GetObject on objects; remove any explicit Deny.',
			0,
			$e
		);
	}
	return $array;
}
