<?php
require_once(__DIR__ . '/includes/loader.php');

if (isset($_GET['id']))
	$id = intval($_GET['id']);
else
	return false;
//Bypasses database settings.
if (isset($_GET['force-weather']))
	$manualWeatherFlag = intval($_GET['force-weather']);
else
	$manualWeatherFlag = null;

require_once(__DIR__ . '/templates/header.php');

function getWeatherData()
{
	//Check for file in with this ID in ./cache/weather/
	$filename = './cache/weather/' . WEATHER_GRID_ID . '_' . WEATHER_GRID_X . '_' . WEATHER_GRID_Y . '.json';

	//If file exists and is within the timeout range.
	if (file_exists($filename) && time() - filemtime($filename) < FSCACHE_WEATHER_CACHE_PERIOD)
		$decodedData = json_decode(file_get_contents($filename));
	else //No valid file to use. Download it.
	{
		if (getWeatherAPIStatus() === true)
		{
			$url = 'https://api.weather.gov/gridpoints/' . WEATHER_GRID_ID . '/' . WEATHER_GRID_X . ',' . WEATHER_GRID_Y . '/forecast';
			$rawWeatherData = curlContent($url);
			$decodedData = json_decode($rawWeatherData);
			if ($decodedData !== false && $decodedData !== null)
				file_put_contents($filename, $rawWeatherData);
			elseif (file_exists($filename))
				$decodedData = json_decode(file_get_contents($filename));
			else
				return false;
		}
		elseif (file_exists($filename))
			$decodedData = json_decode(file_get_contents($filename));
		else
			return false;
	}

	return $decodedData;
}

function parseWeatherData(object $weatherData): string
{
	$weatherHTML = '';
	$time = '';
	$previousDayTime = null;
	$weatherIcons = WEATHER_ICONS;
	$periodLimit = 14;
	foreach ($weatherData->properties->periods as $weatherPeriod)
	{
		$weatherPeriod->number = intval($weatherPeriod->number);
		if ($weatherPeriod->number > 14)
			continue;
		if ($weatherPeriod->number === 1 && $weatherPeriod->name === 'Tonight') //Additional period count if it starts at "tonight" to prevent overflow issues.
			--$periodLimit;


		if ($weatherPeriod->isDaytime === true && $previousDayTime !== null)
		{
			// next day. Close group.
			$weatherHTML .= <<<HTML
				</div>
				HTML;
		}
		if ($previousDayTime === null || $weatherPeriod->isDaytime === true)
		{
			$weatherHTML .= <<<HTML
				<div class="weather-group">
				HTML;
		}
		if ($weatherPeriod->isDaytime === true)
			$time = 'day';
		else
			$time = 'night';

		//Creating header, only if it's not a night period. (Can't use $time incase it is "tonight")
		if (str_contains($weatherPeriod->name, ' Night') === false && $weatherPeriod->name !== 'Tonight')
		{
			if ($weatherPeriod->number !== 2 && in_array($weatherPeriod->name, ['Today', 'This Afternoon']) === false)
				$dayTitleHTML = '<h5>' . substr($weatherPeriod->name, 0, 3) . '</h5>';
			else
				$dayTitleHTML = '<h5>' . $weatherPeriod->name . '</h5>';
		}
		else
			$dayTitleHTML = '';

		//Getting period icon name.
		if (str_contains($weatherPeriod->shortForecast, ' then ') === true)
			$forecastString = strstr($weatherPeriod->shortForecast, ' then ', true);
		else
			$forecastString = $weatherPeriod->shortForecast;
		if (isset($weatherIcons[$time][$forecastString]))
			$iconFileName = $weatherIcons[$time][$forecastString];
		else
			$iconFileName = $weatherIcons[$time]['default'];

		// //Formatting temperature trend.
		// if ($weatherPeriod->temperatureTrend === null)
		// 	$weatherPeriod->temperatureTrend = '';
		// else
		// 	$weatherPeriod->temperatureTrend = ' (' . $weatherPeriod->temperatureTrend . ')';
		//Formatting wind.
		$weatherPeriod->windSpeed = str_replace(' to ', '-', strstr($weatherPeriod->windSpeed, ' mph', true));
		$appRoot = APP_ROOT;
		$weatherHTML .= <<<HTML
			<div class="weather-period weather-{$time}">
				{$dayTitleHTML}
				<div class="weather-information">
					<img title="{$weatherPeriod->shortForecast} - {$time}" src="{$appRoot}assets/weather/{$iconFileName}"/>
					<div title="Temperature" class="weather-temperature">
						<img class="weather-temperature-icon" src="{$appRoot}assets/weather/fahrenheit-line.svg"/>
						{$weatherPeriod->temperature}</div>
					<div title="Humidity" class="weather-humidity">
						<img class="weather-humidity-icon" src="{$appRoot}assets/weather/water-percent-line.svg"/>
						{$weatherPeriod->relativeHumidity->value}%
					</div>
					<div title="Wind" class="weather-wind">
						<img class="weather-wind-icon" src="{$appRoot}assets/weather/windy-line.svg"/>
						{$weatherPeriod->windSpeed}
					</div>
				</div>
			</div>
			HTML;
		// <!-- <div class="weather-forecast"><span class="text-lg font-bold">Forecast:</span> {$weatherPeriod->detailedForecast} ({$weatherPeriod->shortForecast})</div> -->
		$previousDayTime = $weatherPeriod->isDaytime;
	}

	return $weatherHTML;
}


if (WEATHER_ENABLE === false || $manualWeatherFlag === 0) //Not enabled globally, or manually disabled.
	$weatherHTML = '';
elseif ($id === 0 || $manualWeatherFlag === 1 || $connection->getCalendarWeatherToggle($id) === true) //Manually enabled or enabled via DB.
{
	$weatherData = getWeatherData();
	if ($weatherData !== false && $weatherData !== null)
		$weatherHTML = parseWeatherData($weatherData);
	else
		$weatherHTML = '';
}
else
	$weatherHTML = '';

if (WEATHER_ENABLE === false || $manualWeatherFlag === 0 || $weatherDBToggle === false || $weatherHTML !== '')
	$weatherUpdateRate = UI_WEATHER_UPDATE_RATE;
else
	$weatherUpdateRate = UI_WEATHER_RECOVERY_RATE;

if ($manualWeatherFlag !== null)
	$weatherFlagHTML = '&force-weather=' . $manualWeatherFlag;
else
	$weatherFlagHTML = '';
$currentUpdateTime = Date(UI_DATE_GROUP_HEADER, strtotime($weatherData->properties->updated));

echo <<<HTML
	<div id="weather" hx-trigger="click queue:none, every {$weatherUpdateRate}s queue:none" hx-get="weather.php?id={$id}{$weatherFlagHTML}" hx-select="#weather" hx-target="#weather" hx-swap="outerHTML">
		{$weatherHTML}
	</div>
	HTML;

require_once(__DIR__ . '/templates/footer.php');
