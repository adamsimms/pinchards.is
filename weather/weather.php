<?php

$curl = curl_init();

curl_setopt_array($curl, [
	CURLOPT_URL => "https://dark-sky.p.rapidapi.com/47.7119,-52.7254?lang=en&units=auto",
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "GET",
	CURLOPT_HTTPHEADER => [
		"x-rapidapi-host: dark-sky.p.rapidapi.com",
		"x-rapidapi-key: 26d467b295mshd77c3c09e6302e3p13176ejsn728f8369ee06"
	],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
	echo "cURL Error #:" . $err;
} else {
	echo $response;
}

?>
