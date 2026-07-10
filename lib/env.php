<?php

declare(strict_types=1);

/**
 * Local secrets (AWS, Maps API key, etc.). Loaded before bootstrap and by pages that need env without S3.
 *
 * Search order (first readable file wins):
 *   1. PINCHARD_SECRETS_FILE env var (absolute path)
 *   2. ~/.config/pinchards.is/secrets.local.php (outside web document root on shared hosting)
 *   3. secrets.local.php in site root (local dev)
 */
$pinchardRoot = dirname(__DIR__);

$pinchardSecretsCandidates = [];
$pinchardEnvSecrets = getenv('PINCHARD_SECRETS_FILE');
if (is_string($pinchardEnvSecrets) && $pinchardEnvSecrets !== '') {
	$pinchardSecretsCandidates[] = $pinchardEnvSecrets;
}

$pinchardHome = getenv('HOME');
if (is_string($pinchardHome) && $pinchardHome !== '') {
	$pinchardSecretsCandidates[] = rtrim($pinchardHome, '/') . '/.config/pinchards.is/secrets.local.php';
}

$pinchardParent = dirname($pinchardRoot);
if ($pinchardParent !== $pinchardRoot) {
	$pinchardSecretsCandidates[] = $pinchardParent . '/.config/pinchards.is/secrets.local.php';
}

$pinchardSecretsCandidates[] = $pinchardRoot . '/secrets.local.php';

foreach ($pinchardSecretsCandidates as $pinchardSecretsFile) {
	if (is_readable($pinchardSecretsFile)) {
		require $pinchardSecretsFile;
		break;
	}
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
