<?php

declare(strict_types=1);

/**
 * Pinchard core bootstrap: error defaults, optional local AWS env, Composer autoload, S3 client, helpers.
 * Minisites continue to load this via ../functions_inc.php (shim at repo root).
 */

ini_set('display_errors', '1');
error_reporting(E_ALL);

function pinchard_root(): string
{
	return dirname(__DIR__);
}

function pinchard_config(): array
{
	static $cfg;
	return $cfg ??= require __DIR__ . '/config.php';
}

$awsEnv = pinchard_root() . '/aws-env.local.php';
if (is_readable($awsEnv)) {
	require $awsEnv;
}

require pinchard_root() . '/vendor/aws/vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

$s3 = new S3Client([
	'region' => getenv('AWS_DEFAULT_REGION') ?: 'us-east-1',
	'version' => '2006-03-01',
]);

function getObjectList(string $bucket): array
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
						$dateString = explode('_', $dateString)[0];
						if (strrpos($dateString, '/')) {
							$dateString = substr($dateString, strrpos($dateString, '/') + 1);
						}

						$date = DateTime::createFromFormat('Y-m-d\TH:i:s.000\Z', $dateString);
						if ($date === false) {
							continue;
						}
						$date = $date->format('Y/m/d H:i:s');
						$array[] = [
							'filename' => $key,
							'date' => $date,
						];
					}
				}
			}
			$token = !empty($objects['IsTruncated']) ? ($objects['NextContinuationToken'] ?? null) : null;
		} while ($token !== null);
	} catch (AwsException $e) {
		$code = $e->getAwsErrorCode() ?: $e->getCode();
		$msg = $e->getAwsErrorMessage() ?: $e->getMessage();
		$hint = ' For ListBucket errors, fix IAM: the principal needs s3:ListBucket on the bucket and s3:GetObject on objects; remove any explicit Deny.';
		if ($code === 'InvalidAccessKeyId') {
			$hint = ' The access key ID is not valid in AWS (deleted, rotated, or typo). Create a new access key for your IAM user in the AWS console and update aws-env.local.php on the server (AWS_ACCESS_KEY_ID / AWS_SECRET_ACCESS_KEY), or fix DreamHost env vars if you use those instead.';
		}
		throw new RuntimeException(
			'S3 access failed (' . $code . '): ' . $msg . ' —' . $hint,
			0,
			$e
		);
	}
	return $array;
}
