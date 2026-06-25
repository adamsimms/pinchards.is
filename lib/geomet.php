<?php

declare(strict_types=1);

/** Pinchard's Island (GeoNames / info.php map). */
const PINCHARD_WEATHER_LAT = 49.2006;
const PINCHARD_WEATHER_LON = -53.4869;

const PINCHARD_GEOMET_BASE = 'https://api.weather.gc.ca';

/**
 * Recursively prefer English (`en`) from MSC GeoMet bilingual objects.
 *
 * @return mixed
 */
function pinchard_geomet_en(mixed $value): mixed
{
	if (!is_array($value)) {
		return $value;
	}
	if (array_key_exists('en', $value) && array_key_exists('fr', $value)) {
		return $value['en'];
	}
	$out = [];
	foreach ($value as $key => $child) {
		$out[$key] = pinchard_geomet_en($child);
	}
	return $out;
}

/** @return array<string, mixed>|null Decoded JSON object, or null on failure. */
function pinchard_geomet_fetch_json(string $url): ?array
{
	$curl = curl_init();
	curl_setopt_array($curl, [
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 5,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_HTTPHEADER => [
			'Accept: application/json',
			'User-Agent: pinchards.is/weather (MSC GeoMet; contact via pinchards.is)',
		],
	]);

	$body = curl_exec($curl);
	$err = curl_error($curl);
	$code = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);

	if ($err || !is_string($body) || $body === '' || $code >= 400) {
		return null;
	}

	$data = json_decode($body, true);
	return is_array($data) ? $data : null;
}

/**
 * Bbox string for OGC API Features queries: minLon,minLat,maxLon,maxLat.
 */
function pinchard_geomet_bbox(float $lat, float $lon, float $pad = 0.2): string
{
	return sprintf(
		'%F,%F,%F,%F',
		$lon - $pad,
		$lat - $pad,
		$lon + $pad,
		$lat + $pad,
	);
}

/** @return array<string, mixed>|null Nearest citypage weather feature properties. */
function pinchard_geomet_citypage(float $lat, float $lon): ?array
{
	$bbox = pinchard_geomet_bbox($lat, $lon);
	$url = PINCHARD_GEOMET_BASE . '/collections/citypageweather-realtime/items'
		. '?f=json&limit=1&bbox=' . rawurlencode($bbox);

	$data = pinchard_geomet_fetch_json($url);
	$features = $data['features'] ?? null;
	if (!is_array($features) || $features === []) {
		return null;
	}

	$props = $features[0]['properties'] ?? null;
	return is_array($props) ? $props : null;
}

/** @return array<string, mixed>|null Nearest marine forecast feature properties. */
function pinchard_geomet_marine(float $lat, float $lon): ?array
{
	$bbox = pinchard_geomet_bbox($lat, $lon, 0.5);
	$url = PINCHARD_GEOMET_BASE . '/collections/marineweather-realtime/items'
		. '?f=json&limit=1&bbox=' . rawurlencode($bbox);

	$data = pinchard_geomet_fetch_json($url);
	$features = $data['features'] ?? null;
	if (!is_array($features) || $features === []) {
		return null;
	}

	$props = $features[0]['properties'] ?? null;
	return is_array($props) ? $props : null;
}

/**
 * @param array<string, mixed> $current
 * @return array<string, mixed>
 */
function pinchard_geomet_normalize_current(array $current): array
{
	$current = pinchard_geomet_en($current);

	return [
		'time' => $current['timestamp'] ?? null,
		'temperature_c' => $current['temperature']['value'] ?? null,
		'dewpoint_c' => $current['dewpoint']['value'] ?? null,
		'humidity_percent' => $current['relativeHumidity']['value'] ?? null,
		'wind_speed_kmh' => $current['wind']['speed']['value'] ?? null,
		'wind_gust_kmh' => $current['wind']['gust']['value'] ?? null,
		'wind_bearing_deg' => $current['wind']['bearing']['value'] ?? null,
		'wind_direction' => $current['wind']['direction']['value'] ?? null,
		'pressure_kpa' => $current['pressure']['value'] ?? null,
		'pressure_tendency' => $current['pressure']['tendency'] ?? null,
		'wind_chill_c' => $current['windChill']['value'] ?? null,
		'station' => $current['station'] ?? null,
	];
}

/**
 * Build normalized weather JSON for weather/weather.php.
 *
 * @return array<string, mixed>
 */
function pinchard_geomet_weather_payload(float $lat = PINCHARD_WEATHER_LAT, float $lon = PINCHARD_WEATHER_LON): array
{
	$citypage = pinchard_geomet_citypage($lat, $lon);
	if ($citypage === null) {
		return [
			'error' => 'MSC GeoMet citypage weather unavailable',
			'source' => 'msc-geomet',
		];
	}

	$citypageEn = pinchard_geomet_en($citypage);
	$marine = pinchard_geomet_marine($lat, $lon);
	$marineEn = $marine !== null ? pinchard_geomet_en($marine) : null;

	$payload = [
		'source' => 'msc-geomet',
		'attribution' => [
			'provider' => 'Environment and Climate Change Canada',
			'endpoint' => PINCHARD_GEOMET_BASE,
			'license' => 'https://www.canada.ca/en/transparency/terms.html',
		],
		'location' => [
			'name' => "Pinchard's Island",
			'latitude' => $lat,
			'longitude' => $lon,
			'citypage' => [
				'identifier' => $citypageEn['identifier'] ?? null,
				'name' => $citypageEn['name'] ?? null,
				'region' => $citypageEn['region'] ?? null,
				'url' => $citypageEn['url'] ?? null,
			],
		],
		'last_updated' => $citypageEn['lastUpdated'] ?? null,
		'currently' => pinchard_geomet_normalize_current($citypage['currentConditions'] ?? []),
		'forecast' => $citypageEn['forecastGroup'] ?? null,
		'hourly' => $citypageEn['hourlyForecastGroup'] ?? null,
		'warnings' => $citypageEn['warnings'] ?? [],
		'sun' => $citypageEn['riseSet'] ?? null,
	];

	if ($marineEn !== null) {
		$payload['marine'] = [
			'last_updated' => $marineEn['lastUpdated'] ?? null,
			'area' => $marineEn['area'] ?? null,
			'regular_forecast' => $marineEn['regularForecast'] ?? null,
			'extended_forecast' => $marineEn['extendedForecast'] ?? null,
		];
	}

	return $payload;
}

/**
 * Format an MSC GeoMet ISO timestamp for adrift's updateSun() (Newfoundland local time).
 */
function pinchard_geomet_adrift_last_updated(?string $iso): string
{
	if ($iso === null || $iso === '') {
		return (new DateTime('now', new DateTimeZone('America/St_Johns')))->format('Y-m-d H:i');
	}

	try {
		$dt = new DateTime($iso);
		$dt->setTimezone(new DateTimeZone('America/St_Johns'));

		return $dt->format('Y-m-d H:i');
	} catch (Exception) {
		return (new DateTime('now', new DateTimeZone('America/St_Johns')))->format('Y-m-d H:i');
	}
}

/**
 * WeatherAPI-shaped JSON for adrift/ (IonVR live_data.current fields).
 *
 * @return array<string, mixed>
 */
function pinchard_geomet_adrift_live_data(float $lat = PINCHARD_WEATHER_LAT, float $lon = PINCHARD_WEATHER_LON): array
{
	$payload = pinchard_geomet_weather_payload($lat, $lon);

	if (isset($payload['error'])) {
		return $payload;
	}

	$currently = $payload['currently'] ?? [];
	$windKph = $currently['wind_speed_kmh'] ?? null;
	$windDegree = $currently['wind_bearing_deg'] ?? null;
	$windDir = $currently['wind_direction'] ?? null;
	$lastUpdated = pinchard_geomet_adrift_last_updated(
		is_string($currently['time'] ?? null) ? $currently['time'] : ($payload['last_updated'] ?? null),
	);

	$gustKph = $currently['wind_gust_kmh'] ?? null;
	$tempC = $currently['temperature_c'] ?? null;
	$humidity = $currently['humidity_percent'] ?? null;
	$pressureKpa = $currently['pressure_kpa'] ?? null;
	$windChill = $currently['wind_chill_c'] ?? null;

	return [
		'source' => 'msc-geomet',
		'current' => [
			'cloud' => 0,
			'feelslike_c' => $windChill ?? $tempC,
			'feelslike_f' => $windChill !== null ? round(($windChill * 9 / 5) + 32, 1) : ($tempC !== null ? round(($tempC * 9 / 5) + 32, 1) : null),
			'gust_kph' => $gustKph,
			'gust_mph' => $gustKph !== null ? round($gustKph * 0.621371, 1) : null,
			'humidity' => $humidity,
			'is_day' => 1,
			'last_updated' => $lastUpdated,
			'last_updated_epoch' => null,
			'precip_in' => 0,
			'precip_mm' => 0,
			'pressure_in' => $pressureKpa !== null ? round($pressureKpa * 0.2953, 2) : null,
			'pressure_mb' => $pressureKpa !== null ? round($pressureKpa * 10, 0) : null,
			'temp_c' => $tempC,
			'temp_f' => $tempC !== null ? round(($tempC * 9 / 5) + 32, 1) : null,
			'uv' => 0,
			'vis_km' => 10,
			'vis_miles' => 6,
			'wind_degree' => $windDegree !== null ? (int) round((float) $windDegree) : null,
			'wind_dir' => is_string($windDir) ? $windDir : '',
			'wind_kph' => $windKph !== null ? (float) $windKph : 0,
			'wind_mph' => $windKph !== null ? round($windKph * 0.621371, 1) : null,
		],
	];
}
