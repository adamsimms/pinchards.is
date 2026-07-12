<?php

declare(strict_types=1);

/**
 * Historical weather for Cloudberry photos via Open-Meteo ERA5 archive.
 * Always keyed to the cabin location; hour cache shared across photos.
 */

function pinchard_weather_cache_path(): string
{
	return pinchard_photo_cache_dir() . '/weather-hours.json';
}

/**
 * @return array<string, array<string, mixed>>
 */
function pinchard_weather_cache_read(): array
{
	$path = pinchard_weather_cache_path();
	if (!is_readable($path)) {
		return [];
	}

	$decoded = json_decode((string) file_get_contents($path), true);
	if (!is_array($decoded) || !isset($decoded['hours']) || !is_array($decoded['hours'])) {
		return [];
	}

	$out = [];
	foreach ($decoded['hours'] as $key => $hour) {
		if (is_string($key) && is_array($hour)) {
			$out[$key] = $hour;
		}
	}

	return $out;
}

/**
 * @param array<string, array<string, mixed>> $hours
 */
function pinchard_weather_cache_write(array $hours): void
{
	pinchard_ensure_photo_cache_dir();
	ksort($hours);
	$payload = json_encode([
		'cached_at' => time(),
		'hours' => $hours,
	], JSON_THROW_ON_ERROR);
	file_put_contents(pinchard_weather_cache_path(), $payload, LOCK_EX);
}

function pinchard_weather_timezone(): DateTimeZone
{
	return new DateTimeZone('America/St_Johns');
}

/**
 * Interpret naive EXIF/wall-clock capture time as Newfoundland local time.
 */
function pinchard_weather_local_capture(?DateTime $captureDt): ?DateTime
{
	if ($captureDt === null) {
		return null;
	}

	$tz = pinchard_weather_timezone();
	$local = DateTime::createFromFormat(
		'Y-m-d H:i:s',
		$captureDt->format('Y-m-d H:i:s'),
		$tz
	);

	return $local instanceof DateTime ? $local : null;
}

/** Round capture time to nearest archive hour key (`Y-m-d\TH`). */
function pinchard_weather_hour_key(DateTime $localCapture): string
{
	$rounded = clone $localCapture;
	$seconds = (int) $rounded->format('i') * 60 + (int) $rounded->format('s');
	if ($seconds >= 1800) {
		$rounded->modify('+1 hour');
	}
	$rounded->setTime((int) $rounded->format('G'), 0, 0);

	return $rounded->format('Y-m-d\TH');
}

/**
 * @return array<int, string>
 */
function pinchard_weather_wmo_labels(): array
{
	return [
		0 => 'Clear sky',
		1 => 'Mainly clear',
		2 => 'Partly cloudy',
		3 => 'Overcast',
		45 => 'Fog',
		48 => 'Depositing rime fog',
		51 => 'Light drizzle',
		53 => 'Moderate drizzle',
		55 => 'Dense drizzle',
		56 => 'Light freezing drizzle',
		57 => 'Dense freezing drizzle',
		61 => 'Slight rain',
		63 => 'Moderate rain',
		65 => 'Heavy rain',
		66 => 'Light freezing rain',
		67 => 'Heavy freezing rain',
		71 => 'Slight snow',
		73 => 'Moderate snow',
		75 => 'Heavy snow',
		77 => 'Snow grains',
		80 => 'Slight rain showers',
		81 => 'Moderate rain showers',
		82 => 'Violent rain showers',
		85 => 'Slight snow showers',
		86 => 'Heavy snow showers',
		95 => 'Thunderstorm',
		96 => 'Thunderstorm with slight hail',
		99 => 'Thunderstorm with heavy hail',
	];
}

function pinchard_weather_wmo_label(?int $code): string
{
	if ($code === null) {
		return 'Unknown';
	}
	$labels = pinchard_weather_wmo_labels();

	return $labels[$code] ?? ('Code ' . $code);
}

function pinchard_weather_compass(?float $degrees): string
{
	if ($degrees === null) {
		return '';
	}
	$dirs = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];

	return $dirs[(int) round($degrees / 22.5) % 16];
}

function pinchard_weather_format_temp(?float $celsius): string
{
	if ($celsius === null) {
		return '';
	}
	// Unicode minus for negatives; keep one decimal to match archive samples.
	$formatted = number_format(abs($celsius), 1, '.', '');
	if ($celsius < 0) {
		return '−' . $formatted . '°C';
	}

	return $formatted . '°C';
}

function pinchard_weather_format_speed(?float $kmh): string
{
	if ($kmh === null) {
		return '';
	}
	if ($kmh >= 10) {
		return (string) (int) round($kmh);
	}

	return number_format($kmh, 1, '.', '');
}

/**
 * @param array<string, mixed> $hour
 */
function pinchard_weather_precipitation_line(array $hour): ?string
{
	$precip = isset($hour['precipitation']) && is_numeric($hour['precipitation'])
		? (float) $hour['precipitation']
		: 0.0;
	$rain = isset($hour['rain']) && is_numeric($hour['rain'])
		? (float) $hour['rain']
		: 0.0;
	$snowfall = isset($hour['snowfall']) && is_numeric($hour['snowfall'])
		? (float) $hour['snowfall']
		: 0.0;

	if ($precip <= 0 && $rain <= 0 && $snowfall <= 0) {
		return null;
	}

	$parts = [];
	if ($snowfall > 0) {
		$mm = $precip > 0 ? $precip : $snowfall;
		$parts[] = rtrim(rtrim(number_format($mm, 2, '.', ''), '0'), '.') . ' mm snow';
	} elseif ($rain > 0) {
		$parts[] = rtrim(rtrim(number_format($rain, 2, '.', ''), '0'), '.') . ' mm rain';
	} elseif ($precip > 0) {
		$parts[] = rtrim(rtrim(number_format($precip, 2, '.', ''), '0'), '.') . ' mm';
	}

	if ($parts === []) {
		return null;
	}

	return 'Precipitation: ' . implode(' · ', $parts);
}

/**
 * Quiet sky/humidity line from fields already fetched for the hour cache.
 *
 * @param array<string, mixed> $hour
 */
function pinchard_weather_sky_line(array $hour): ?string
{
	$parts = [];
	if (isset($hour['cloud_cover']) && is_numeric($hour['cloud_cover'])) {
		$cloud = (float) $hour['cloud_cover'];
		if ($cloud >= 85) {
			$parts[] = 'Heavy cloud';
		} elseif ($cloud >= 50) {
			$parts[] = 'Broken cloud';
		} elseif ($cloud >= 20) {
			$parts[] = 'Thin cloud';
		} else {
			$parts[] = 'Clear skies';
		}
	}
	if (isset($hour['relative_humidity_2m']) && is_numeric($hour['relative_humidity_2m'])) {
		$rh = (float) $hour['relative_humidity_2m'];
		if ($rh >= 85) {
			$parts[] = 'damp air';
		} elseif ($rh <= 35) {
			$parts[] = 'dry air';
		}
	}
	if ($parts === []) {
		return null;
	}

	return 'Sky: ' . implode(' · ', $parts);
}

/**
 * @param array<string, mixed> $hour
 */
function pinchard_weather_format_html(array $hour): string
{
	$temp = isset($hour['temperature_2m']) && is_numeric($hour['temperature_2m'])
		? (float) $hour['temperature_2m']
		: null;
	$code = isset($hour['weather_code']) && is_numeric($hour['weather_code'])
		? (int) $hour['weather_code']
		: null;
	$windSpeed = isset($hour['wind_speed_10m']) && is_numeric($hour['wind_speed_10m'])
		? (float) $hour['wind_speed_10m']
		: null;
	$windDir = isset($hour['wind_direction_10m']) && is_numeric($hour['wind_direction_10m'])
		? (float) $hour['wind_direction_10m']
		: null;
	$gusts = isset($hour['wind_gusts_10m']) && is_numeric($hour['wind_gusts_10m'])
		? (float) $hour['wind_gusts_10m']
		: null;

	if ($temp === null || $windSpeed === null) {
		return '';
	}

	$lines = [];
	$lines[] = 'Conditions: ' . pinchard_weather_format_temp($temp) . ' · ' . pinchard_h(pinchard_weather_wmo_label($code));

	$wind = 'Wind: ' . pinchard_h(pinchard_weather_compass($windDir)) . ' '
		. pinchard_h(pinchard_weather_format_speed($windSpeed)) . ' km/h';
	if ($gusts !== null) {
		$wind .= ' · gusts ' . pinchard_h(pinchard_weather_format_speed($gusts)) . ' km/h';
	}
	$lines[] = $wind;

	$precipLine = pinchard_weather_precipitation_line($hour);
	if ($precipLine !== null) {
		$lines[] = pinchard_h($precipLine);
	}

	$skyLine = pinchard_weather_sky_line($hour);
	if ($skyLine !== null) {
		$lines[] = pinchard_h($skyLine);
	}

	return implode('<br>', $lines);
}

/**
 * Fetch hourly ERA5 data for an inclusive local-date range.
 *
 * @return array<string, array<string, mixed>> hours keyed Y-m-d\TH
 */
function pinchard_weather_fetch_range(string $startDay, string $endDay): array
{
	$cabin = pinchard_cloudberry_cabin_coords();
	$query = http_build_query([
		'latitude' => $cabin['lat'],
		'longitude' => $cabin['lon'],
		'start_date' => $startDay,
		'end_date' => $endDay,
		'hourly' => implode(',', [
			'temperature_2m',
			'weather_code',
			'precipitation',
			'rain',
			'snowfall',
			'wind_speed_10m',
			'wind_direction_10m',
			'wind_gusts_10m',
			'cloud_cover',
			'relative_humidity_2m',
		]),
		'cell_selection' => 'nearest',
		'timezone' => 'America/St_Johns',
	], '', '&', PHP_QUERY_RFC3986);

	$url = 'https://archive-api.open-meteo.com/v1/archive?' . $query;
	$timeout = $startDay === $endDay ? 8 : 30;
	$context = stream_context_create([
		'http' => [
			'timeout' => $timeout,
			'follow_location' => 1,
			'header' => "Accept: application/json\r\nUser-Agent: pinchards.is weather cache\r\n",
		],
		'ssl' => [
			'verify_peer' => true,
			'verify_peer_name' => true,
		],
	]);

	$raw = @file_get_contents($url, false, $context);
	if ($raw === false || $raw === '') {
		return [];
	}

	$decoded = json_decode($raw, true);
	if (!is_array($decoded) || !isset($decoded['hourly']) || !is_array($decoded['hourly'])) {
		return [];
	}

	$hourly = $decoded['hourly'];
	$times = $hourly['time'] ?? null;
	if (!is_array($times) || $times === []) {
		return [];
	}

	$gridLat = isset($decoded['latitude']) && is_numeric($decoded['latitude'])
		? (float) $decoded['latitude']
		: null;
	$gridLon = isset($decoded['longitude']) && is_numeric($decoded['longitude'])
		? (float) $decoded['longitude']
		: null;

	$hours = [];
	foreach ($times as $i => $time) {
		if (!is_string($time) || !isset($hourly['temperature_2m'][$i]) || $hourly['temperature_2m'][$i] === null) {
			continue;
		}
		$dt = DateTime::createFromFormat('Y-m-d\TH:i', $time, pinchard_weather_timezone());
		if (!$dt instanceof DateTime) {
			continue;
		}
		$key = $dt->format('Y-m-d\TH');
		$hours[$key] = [
			'time' => $time,
			'temperature_2m' => $hourly['temperature_2m'][$i] ?? null,
			'weather_code' => $hourly['weather_code'][$i] ?? null,
			'precipitation' => $hourly['precipitation'][$i] ?? null,
			'rain' => $hourly['rain'][$i] ?? null,
			'snowfall' => $hourly['snowfall'][$i] ?? null,
			'wind_speed_10m' => $hourly['wind_speed_10m'][$i] ?? null,
			'wind_direction_10m' => $hourly['wind_direction_10m'][$i] ?? null,
			'wind_gusts_10m' => $hourly['wind_gusts_10m'][$i] ?? null,
			'cloud_cover' => $hourly['cloud_cover'][$i] ?? null,
			'relative_humidity_2m' => $hourly['relative_humidity_2m'][$i] ?? null,
			'grid_lat' => $gridLat,
			'grid_lon' => $gridLon,
		];
	}

	return $hours;
}

/**
 * Fetch one calendar day of hourly ERA5 data.
 *
 * @return array<string, array<string, mixed>> hours keyed Y-m-d\TH
 */
function pinchard_weather_fetch_day(string $day): array
{
	return pinchard_weather_fetch_range($day, $day);
}

/**
 * Merge fetched hours into the on-disk cache. Returns hours written this call.
 *
 * @param array<string, array<string, mixed>> $hours
 * @return int number of hour keys now present for the requested set
 */
function pinchard_weather_cache_merge(array $hours): int
{
	if ($hours === []) {
		return 0;
	}
	$cache = pinchard_weather_cache_read();
	$cache = array_merge($cache, $hours);
	pinchard_weather_cache_write($cache);

	return count($hours);
}

/**
 * @return array{html: string, hourKey: string, data: array<string, mixed>}|null
 */
function pinchard_weather_for_capture(?DateTime $captureDt): ?array
{
	$local = pinchard_weather_local_capture($captureDt);
	if ($local === null) {
		return null;
	}

	$hourKey = pinchard_weather_hour_key($local);
	$cache = pinchard_weather_cache_read();

	if (!isset($cache[$hourKey]) || !is_array($cache[$hourKey])) {
		$day = substr($hourKey, 0, 10);
		$fetched = pinchard_weather_fetch_day($day);
		if ($fetched === []) {
			return null;
		}
		try {
			pinchard_weather_cache_merge($fetched);
		} catch (Throwable) {
			// Cache write failure should not hide weather for this request.
		}
		$cache = array_merge($cache, $fetched);
	}

	$hour = $cache[$hourKey] ?? null;
	if (!is_array($hour)) {
		return null;
	}

	$html = pinchard_weather_format_html($hour);
	if ($html === '') {
		return null;
	}

	return [
		'html' => $html,
		'hourKey' => $hourKey,
		'data' => $hour,
	];
}
