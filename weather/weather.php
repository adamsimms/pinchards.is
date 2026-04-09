<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/env.php';

header('Content-Type: application/json; charset=utf-8');

$key = pinchard_env_non_empty('RAPIDAPI_KEY');
if ($key === null) {
	http_response_code(500);
	echo json_encode(['error' => 'RAPIDAPI_KEY not configured. Add it to secrets.local.php (see secrets.local.php.example).']);
	exit;
}

$curl = curl_init();

curl_setopt_array($curl, [
	CURLOPT_URL => 'https://dark-sky.p.rapidapi.com/47.7119,-52.7254?lang=en&units=auto',
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_ENCODING => '',
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => 'GET',
	CURLOPT_HTTPHEADER => [
		'x-rapidapi-host: dark-sky.p.rapidapi.com',
		'x-rapidapi-key: ' . $key,
	],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($err) {
	http_response_code(502);
	echo json_encode(['error' => 'cURL error', 'detail' => $err]);
	exit;
}

if ($code >= 400) {
	http_response_code($code);
}

echo $response !== false ? $response : json_encode(['error' => 'Empty response']);
