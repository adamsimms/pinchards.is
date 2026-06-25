<?php

declare(strict_types=1);

/**
 * Pinchard core bootstrap: error defaults, optional local AWS env, Composer autoload, S3 client, helpers.
 * Minisites continue to load this via ../functions_inc.php (shim at repo root).
 */

require_once __DIR__ . '/env.php';

if (pinchard_env_non_empty('PINCHARD_DEBUG') === '1') {
	ini_set('display_errors', '1');
} else {
	ini_set('display_errors', '0');
	ini_set('log_errors', '1');
}
error_reporting(E_ALL);

require_once __DIR__ . '/helpers.php';

function pinchard_root(): string
{
	return dirname(__DIR__);
}

function pinchard_config(): array
{
	static $cfg;
	return $cfg ??= require __DIR__ . '/config.php';
}

require pinchard_root() . '/vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

$awsKey = pinchard_env_non_empty('AWS_ACCESS_KEY_ID');
$awsSecret = pinchard_env_non_empty('AWS_SECRET_ACCESS_KEY');
$awsRegion = pinchard_env_non_empty('AWS_DEFAULT_REGION') ?? 'us-east-1';

$s3Config = [
	'region' => $awsRegion,
	'version' => '2006-03-01',
];

if ($awsKey !== null && $awsSecret !== null) {
	$creds = ['key' => $awsKey, 'secret' => $awsSecret];
	$token = pinchard_env_non_empty('AWS_SESSION_TOKEN');
	if ($token !== null) {
		$creds['token'] = $token;
	}
	$s3Config['credentials'] = $creds;
} else {
	$msg = 'AWS credentials are not set. Add secrets.local.php or fix GitHub Actions secrets (AWS_ACCESS_KEY_ID / AWS_SECRET_ACCESS_KEY).';
	if (pinchard_env_non_empty('PINCHARD_DEBUG') === '1') {
		throw new RuntimeException($msg);
	}
	http_response_code(503);
	header('Content-Type: text/plain; charset=utf-8');
	exit('Photo service is temporarily unavailable.');
}

$s3 = new S3Client($s3Config);

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

						$dt = DateTime::createFromFormat('Y-m-d\TH:i:s.000\Z', $dateString);
						if ($dt === false) {
							continue;
						}
						$date = $dt->format('Y/m/d H:i:s');
						$array[] = [
							'filename' => $key,
							'date' => $date,
							'show_date' => $dt->format('M j @ H:i'),
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
			$hint = ' The access key ID is not valid in AWS (deleted, rotated, or typo). Create a new access key for your IAM user in the AWS console and update secrets.local.php on the server (AWS_ACCESS_KEY_ID / AWS_SECRET_ACCESS_KEY), or fix DreamHost env vars if you use those instead.';
		}
		throw new RuntimeException(
			'S3 access failed (' . $code . '): ' . $msg . ' —' . $hint,
			0,
			$e
		);
	}
	return $array;
}

require_once __DIR__ . '/jam.php';
