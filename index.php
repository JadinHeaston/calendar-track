<?php
require_once(__DIR__ . '/includes/loader.php');

if (isset($_GET['id']))
	$id = intval($_GET['id']);
else
	$id = 0;

$calendars = $connection->getCalendar($id, true);

require_once(__DIR__ . '/templates/header.php');
echo <<<HTML
	<main>
	HTML;

if ($id !== 0 & sizeof($calendars) === 1)
{
	$calendar = $calendars[0];
	unset($calendars);
	//Display the main calendar view.
	$mainHeaderCurrentDatetime = Date(UI_DATE_GROUP_HEADER);
	$currentUpdateTime = Date(UI_DATE_GROUP_HEADER);
	$calendarUpdateRate = UI_CALENDAR_UPDATE_RATE;
	$weatherUpdateRate = UI_WEATHER_UPDATE_RATE;

	//Main Header
	echo <<<HTML
		<h2 id="calendar-name-header">{$calendar['name']}</h2>
		<h3 id="current-time">{$mainHeaderCurrentDatetime}</h3>
		HTML;

	$events = getICSEventData(intval($calendar['id']), $calendar['ics_link']);
	$eventsLastUpdatedTime = getCachedICSLastModificationTime(intval($calendar['id']));
	unset($calendar); //Unsetting calendar ASAP to drop memory usage. All relavent events have been retrieved.
	//Calendar
	echo <<<HTML
		<div id="calendar" hx-trigger="click queue:none, every {$calendarUpdateRate}s queue:none" hx-get="" hx-select="#calendar" hx-target="#calendar" hx-swap="outerHTML">
			<h3 id="event-header">Events</h3>
			<p>({$eventsLastUpdatedTime->format(UI_DATE_GROUP_HEADER)})</p>
		HTML;

	$icalFunctions = new Ical\ICal();

	$currentDay = null;
	/**
	 *  @var ICal\Event
	 */
	foreach ($events as $key => &$event)
	{
		$dtstart = $icalFunctions->iCalDateToDateTime($event->dtstart_array[3]);
		$dtend = $icalFunctions->iCalDateToDateTime($event->dtend_array[3]);
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
		echo <<<HTML
			<div class="cal-event">
				<div class="cal-times">
					<p>{$dtstart->format(UI_DATE_EVENT_TIME)} - {$dtend->format(UI_DATE_EVENT_TIME)}</p>
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

	if (WEATHER_ENABLE === true)
	{
		//Weather
		echo <<<HTML
			<div id="weather" hx-trigger="load queue:none" hx-get="weather.php" hx-select="#weather" hx-target="#weather" hx-swap="outerHTML">
				<h3 id="weather-header">Weather</h3>
			</div>
			HTML;
	}
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
