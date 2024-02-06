<?php
require_once(__DIR__ . '/includes/loader.php');

if (isset($_GET['id']))
	$id = intval($_GET['id']);
else
	$id = 0;
//Bypasses database setting for weather toggle.
if (isset($_GET['force-weather']))
	$manualWeatherFlag = intval($_GET['force-weather']);
else
	$manualWeatherFlag = null;

$calendars = $connection->getCalendar($id, true);

require_once(__DIR__ . '/templates/header.php');
echo <<<HTML
	<main>
	HTML;

if ($id !== 0 & count($calendars) === 1)
{
	$calendar = $calendars[0];
	unset($calendars);
	//Display the main calendar view.
	$mainHeaderCurrentDatetime = Date(UI_DATE_GROUP_HEADER);
	$currentUpdateTime = Date(UI_DATE_GROUP_HEADER);
	$calendarUpdateRate = UI_CALENDAR_UPDATE_RATE;
	$weatherUpdateRate = UI_WEATHER_UPDATE_RATE;

	//Main Header
	if (UI_DISPLAY_CALENDAR_HEADER === true)
	{
		if (UI_LOGO_CALENDAR_HEADER_PATH !== '')
		{
			$calendarHeaderLogo = UI_LOGO_CALENDAR_HEADER_PATH;
			$calendarHeaderLogoHTML = <<<HTML
				<div id="calendar-header-logo">
					<img src="{$calendarHeaderLogo}" alt="Calendar Header Logo">
				</div>
				HTML;
		}
		else
			$calendarHeaderLogoHTML = '';

		echo <<<HTML
		<div id="calendar-header">
			<div id="calendar-header-text">
				<h2 id="calendar-name-header">{$calendar['name']}</h2>
				<h3 id="current-time">{$mainHeaderCurrentDatetime}</h3>
			</div>
			{$calendarHeaderLogoHTML}
		</div>
		HTML;
	}

	$events = getICSEventData(intval($calendar['id']), $calendar['ics_link']);
	$eventsLastUpdatedTime = getCachedICSLastModificationTime(intval($calendar['id']));
	unset($calendar); //Unsetting calendar ASAP to drop memory usage. All relavent events have been retrieved.
	//Calendar
	echo <<<HTML
		<div id="calendar" hx-trigger="click queue:none, every {$calendarUpdateRate}s queue:none" hx-get="?id={$id}" hx-select="#calendar" hx-target="#calendar" hx-swap="outerHTML">
		HTML;

	if (UI_DISPLAY_EVENT_HEADER === true)
	{
		echo <<<HTML
			<h3 id="event-header">Events</h3>
			<p>({$eventsLastUpdatedTime->format(UI_DATE_GROUP_HEADER)})</p>
			HTML;
	}

	$icalFunctions = new Ical\ICal();

	$currentDay = null;
	/**
	 *  @var ICal\Event
	 */
	foreach ($events as $key => &$event)
	{
		$dtstart = $icalFunctions->iCalDateToDateTime($event->dtstart_array[3]);
		$dtend = $icalFunctions->iCalDateToDateTime($event->dtend_array[3]);
		$dtend->setTimezone(new DateTimeZone(date_default_timezone_get()));
		$dtstart->setTimezone(new DateTimeZone(date_default_timezone_get()));

		if ($currentDay !== $dtstart->format('Y-m-d'))
		{
			//Starting new container and displaying day header.
			$currentDay = $dtstart->format('Y-m-d');
			if ($key > 0)
			{
				//Closing previous event-day containers.
				echo <<<HTML
					</div>
					HTML;
			}

			echo <<<HTML
				<div class="event-day">
					<h4 class="event-day-header">{$dtstart->format(UI_DATE_EVENT_HEADER)}</h4>
				HTML;
		}

		//Display event information.
		if ($dtstart <= new DateTime && new DateTime <= $dtend)
		{
			$eventStatus = 'event-current';
		}
		elseif (new DateTime > $dtend)
			$eventStatus = 'event-past';
		else
			$eventStatus = 'event-future';
		echo <<<HTML
			<div class="cal-event {$eventStatus}">
				<div class="cal-times">
					<span>{$dtstart->format(UI_DATE_EVENT_TIME)}</span><span>-</span><span>{$dtend->format(UI_DATE_EVENT_TIME)}</span>
				</div>
				<div class="cal-info">
					<p class="cal-summary">{$event->summary}</p>
				</div>
			</div>
			HTML;
	}

	if (!empty($events))
	{
		//Closing last event-day container.
		echo <<<HTML
			</div>
			HTML;
	}

	//Closing calendar div.
	echo <<<HTML
		</div>
		HTML;

	if ($manualWeatherFlag !== null)
		$weatherFlagHTML = '&force-weather=' . $manualWeatherFlag;
	else
		$weatherFlagHTML = '';

	//Weather
	echo <<<HTML
		<div id="weather" hx-trigger="load queue:none" hx-get="weather.php?id={$id}{$weatherFlagHTML}" hx-select="#weather" hx-target="#weather" hx-swap="outerHTML">
			<h3 id="weather-header">Weather</h3>
		</div>
		HTML;

	//Closing container div.
	echo <<<HTML
		</div>
		HTML;
}
else
{
	//Display the calendar list view.
	$options = '';
	foreach ($calendars as $calendar)
	{
		$calendar['id'] = intval($calendar['id']);
		$options .= <<<HTML
			<option value="{$calendar['id']}">{$calendar['name']}</option>
			HTML;
	}
	echo <<<HTML
		<select placeholder="Calendar Selection" id="calendar-selection" name="id" hx-trigger="change" hx-get="" hx-push-url="true" hx-select="main" hx-target="main" hx-swap="outerHTML">
			<option disabled selected>Calendar Selection</option>
			$options
		</select>
		HTML;
}

echo <<<HTML
	</main>
	HTML;

require_once(__DIR__ . '/templates/footer.php');
