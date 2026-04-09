<?php

declare(strict_types=1);

/**
 * Local secrets (AWS, Maps API key, etc.). Loaded before bootstrap and by pages that need env without S3.
 * Prefer secrets.local.php; aws-env.local.php is still read if present (legacy name).
 */
$pinchardRoot = dirname(__DIR__);
$pinchardSecretsFile = $pinchardRoot . '/secrets.local.php';
if (!is_readable($pinchardSecretsFile)) {
	$pinchardLegacySecrets = $pinchardRoot . '/aws-env.local.php';
	if (is_readable($pinchardLegacySecrets)) {
		$pinchardSecretsFile = $pinchardLegacySecrets;
	}
}
if (is_readable($pinchardSecretsFile)) {
	require $pinchardSecretsFile;
}

/**
 * Read env vars set via putenv(), $_ENV, or the server (some PHP-FPM pools only populate some of these).
 */
function pinchard_env_non_empty(string $name): ?string
{
	$v = getenv($name);
	if (is_string($v) && $v !== '') {
		return $v;
	}
	if (isset($_ENV[$name]) && is_string($_ENV[$name]) && $_ENV[$name] !== '') {
		return $_ENV[$name];
	}
	if (isset($_SERVER[$name]) && is_string($_SERVER[$name]) && $_SERVER[$name] !== '') {
		return $_SERVER[$name];
	}
	return null;
}
