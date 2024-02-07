<?php
if (isHTMX())
	return;

//Create version hashes based on last modified time.
$versionedFiles = array(
	'/../assets/favicon.svg' => '',
	'/../css/tailwind_output.css' => '',
	'/../js/scripts.js' => '',
	'/../vendor/htmx.min.js' => '',
);

foreach ($versionedFiles as $fileName => $hash)
{
	$versionedFiles[$fileName] = substr(md5(filemtime(__DIR__ . $fileName)), 0, 6);
}

$appRoot = APP_ROOT;
$autoRefreshScript = '';
if (UI_FULL_PAGE_RELOAD !== 0 && UI_FULL_PAGE_RELOAD >= 30)
{
	$autoRefreshRate = UI_FULL_PAGE_RELOAD;
	$autoRefreshScript = <<<HTML
		<script>setInterval('location.reload(true)', {$autoRefreshRate}000);</script>
		HTML;
}

$mainBackgroundColor = UI_COLOR_MAIN_BACKGROUND;
$mainTextColor = UI_COLOR_MAIN_TEXT;
$eventBackgroundColor = UI_COLOR_EVENT_BACKGROUND;
$eventTextColor = UI_COLOR_EVENT_TEXT;
$headerBackgroundColor = UI_COLOR_HEADER_BACKGROUND;
$headerTextColor = UI_COLOR_HEADER_TEXT;
$calendarHeaderBackgroundColor = UI_COLOR_CALENDAR_HEADER_BACKGROUND;
$calendarHeaderTextColor = UI_COLOR_CALENDAR_HEADER_TEXT;
$eventDayBackgroundColor = UI_COLOR_EVENT_DAY_HEADER_BACKGROUND;
$eventDayTextColor = UI_COLOR_EVENT_DAY_HEADER_TEXT;
$weatherDayBackgroundColor = UI_COLOR_WEATHER_DAY_BACKGROUND;
$weatherDayTextColor =  UI_COLOR_WEATHER_DAY_TEXT;
$weatherNightBackgroundColor = UI_COLOR_WEATHER_NIGHT_BACKGROUND;
$weatherNightTextColor = UI_COLOR_WEATHER_NIGHT_TEXT;
$calendarHeaderLogoCSS = '';
$backgroundLogoCSS = '';
$embedCSS = '';
if (UI_LOGO_BACKGROUND_PATH !== '')
{
	$backgroundLogo = $appRoot . UI_LOGO_BACKGROUND_PATH;
	$backgroundLogoCSS = <<<CSS
		body {
			z-index: 9000;
		}

		body:before {
			content: " ";
			background: url("{$backgroundLogo}") no-repeat center center fixed;
			display: block;
			position: fixed;
			left: 0;
			top: 0;
			width: 100%;
			height: 100%;
			opacity: 40%;
			z-index: -1;
			pointer-events: none;
		}
		CSS;
}
if (UI_LOGO_CALENDAR_HEADER_PATH !== '')
{
	$calendarHeaderLogoCSS = <<<CSS
		#calendar-header {
			justify-content: space-between;
		}

		#calendar-header>#calendar-header-text{
			text-align: left;
		}
		CSS;
}

if (isset($_GET['embed']))
{
	$embedCSS = <<<CSS
		body {
			overflow: hidden;
		}

		#weather {
			display: none;
		}
		CSS;
}
echo <<<HTML
	<!DOCTYPE html>
	<html lang="en">

	<head>
		<meta charset="UTF-8">
		<title>Calendar Track</title>
		<meta name="viewport" content="width=device-width,initial-scale=1">
		{$autoRefreshScript}
		<link rel="icon" href="{$appRoot}assets/favicon.svg?v={$versionedFiles['/../assets/favicon.svg']}" type="image/svg+xml">
		<link rel="stylesheet" href="{$appRoot}css/tailwind_output.css?v={$versionedFiles['/../css/tailwind_output.css']}">
		<script src="{$appRoot}js/scripts.js?v={$versionedFiles['/../js/scripts.js']}"></script>
		<script src="{$appRoot}vendor/htmx.min.js?v={$versionedFiles['/../vendor/htmx.min.js']}"></script>
		<style>
			:root {
				--main-background-color: {$mainBackgroundColor};
				--main-text-color: {$mainTextColor};
				--event-background-color: {$eventBackgroundColor};
				--event-text-color: {$eventTextColor};
				--header-background-color: {$headerBackgroundColor};
				--header-text-color: {$headerTextColor};
				--calendar-header-background-color: {$calendarHeaderBackgroundColor};
				--calendar-header-text-color: {$calendarHeaderTextColor};
				--event-day-background-color: {$eventDayBackgroundColor};
				--event-day-text-color: {$eventDayTextColor};
				--weather-day-background-color: {$weatherDayBackgroundColor};
				--weather-day-text-color: {$weatherDayTextColor};
				--weather-night-background-color: {$weatherNightBackgroundColor};
				--weather-night-text-color: {$weatherNightTextColor};
			}

			{$calendarHeaderLogoCSS}
			{$backgroundLogoCSS}
			{$embedCSS}
		</style>
	</head>

	<body>
	HTML;

if (UI_DISPLAY_CALTRACK_HEADER === true)
{
	echo <<<HTML
		<header>
			<h1>Calendar Track</h1>
		</header>
		HTML;
}
