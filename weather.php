<?php
require_once(__DIR__ . '/includes/loader.php');
if (WEATHER_ENABLE !== true)
	return false;

require_once(__DIR__ . '/templates/header.php');

function getWeatherData()
{
	if (WEATHER_ENABLE !== true)
		return false;
	//Check for file in with this ID in ./cache/weather/
	$filename = './cache/weather/' . WEATHER_GRID_ID . '_' . WEATHER_GRID_X . '_' . WEATHER_GRID_Y . '.json';

	//If file exists and is within the timeout range.
	if (file_exists($filename) && time() - filemtime($filename) < FSCACHE_WEATHER_CACHE_PERIOD)
	{
		$weatherData = file_get_contents($filename);
	}
	else //No valid file to use. Download it.
	{
		$url = 'https://api.weather.gov/gridpoints/' . WEATHER_GRID_ID . '/' . WEATHER_GRID_X . ',' . WEATHER_GRID_Y . '/forecast';
		$weatherData = curlContent($url);
		file_put_contents($filename, $weatherData);
	}

	return json_decode($weatherData);
}

$weatherUpdateRate = UI_WEATHER_UPDATE_RATE;

$weatherData = getWeatherData();
$weatherHTML = parseWeatherData($weatherData);

function parseWeatherData(object $weatherData)
{
	$weatherHTML = '';
	$previousDayName = '';
	foreach ($weatherData->properties->periods as $weatherPeriod)
	{
		if ($weatherPeriod->temperatureTrend === null)
			$weatherPeriod->temperatureTrend = '';
		else
			$weatherPeriod->temperatureTrend = ' (' . $weatherPeriod->temperatureTrend . ')';
		// $icon = WEATHER_ICONS[($weatherPeriod->isDaytime === true ? 'Day' : 'Night')][$weatherPeriod->shortForecast];
		if ($previousDayName !== '' && $weatherPeriod->name !== $previousDayName . ' Night')
		{
			// next day. Close group.
			$weatherHTML .= <<<HTML
				</div>
				HTML;
		}
		$weatherHTML .= <<<HTML
			<div class="weather-group bg-gray-100 dark:bg-gray-900">
			HTML;
		if ($weatherPeriod->name === $previousDayName . ' Night' || $weatherPeriod->name === 'Tonight')
			$time = 'night';
		else
			$time = 'day';
		$weatherHTML .= <<<HTML
			<div class="weather-{$time}">
				<h5>{$time}</h5>
				<div class="weather-information">
					<div class="weather-temperature"><span class="text-lg font-bold">Temperature:</span> {$weatherPeriod->temperature}{$weatherPeriod->temperatureUnit}{$weatherPeriod->temperatureTrend}</div>
					<div class="weather-humidity"><span class="text-lg font-bold">Humidity:</span> {$weatherPeriod->relativeHumidity->value}%</div>
					<div class="weather-wind"><span class="text-lg font-bold">Wind:</span> {$weatherPeriod->windSpeed} ({$weatherPeriod->windDirection})</div>
					<div class="weather-forecast"><span class="text-lg font-bold">Forecast:</span> {$weatherPeriod->detailedForecast} ({$weatherPeriod->shortForecast})</div>
				</div>
				<!-- <div class="weather-icon"><img src="{$weatherPeriod->icon}"/></div> -->
			</div>
			HTML;
		$previousDayName = $weatherPeriod->name;
	}

	return $weatherHTML;
}

$currentUpdateTime = Date(UI_DATE_GROUP_HEADER, strtotime($weatherData->properties->updated));

echo <<<HTML
	<div id="weather" hx-trigger="click queue:none, every {$weatherUpdateRate}s queue:none" hx-get="weather.php" hx-select="#weather" hx-target="#weather" hx-swap="outerHTML">
		<!-- <h3 id="weather-header">Weather</h3>
		<p>({$currentUpdateTime})</p> -->
		{$weatherHTML}
	</div>
	HTML;

require_once(__DIR__ . '/templates/footer.php');
