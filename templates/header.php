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
	</head>

	<body>
		<header>
			<h1>Calendar Track</h1>
		</header>
	HTML;
