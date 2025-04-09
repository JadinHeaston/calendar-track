<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() !== PHP_SESSION_ACTIVE)
	session_start();
require_once(__DIR__ . '/../includes/loader.php');

require_once(__DIR__ . '/../templates/header.php');

if (empty(CALENDAR_MANAGEMENT_PASSWORD_HASH))
	die('Management disabled. Please set a management password.');

if (isHTMX() === false)
{
	echo <<<HTML
		<main id="management">
		HTML;
}
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true)
{
	//If they are posting a password, check that against
	if (isset($_POST['calendar_edit_password']))
	{
		if (password_verify($_POST['calendar_edit_password'], CALENDAR_MANAGEMENT_PASSWORD_HASH) === true)
			$_SESSION['authenticated'] = true;
		else
		{
			echo <<<HTML
				Incorrect Password. :)
				HTML;
		}
	}

	//If they are still not authenticated...
	if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true)
	{
		//Display login page.
		echo <<<HTML
		<form method="post" action="">
			<label for="calendar_edit_password">Calendar Edit Password</label>
			<input type="password" id="calendar_edit_password" name="calendar_edit_password" placeholder="Password">
			<button type="submit">Submit</button>
			<button type="submit" hx-post="hash_generator.php" hx-target="#hash-output">Hash</button>
		</form>

		<div id="hash-output"></div>
		HTML;
	}
}
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true)
{
	if (isset($_GET['id']) === false)
		$_GET['id'] = [];

	if (isset($_GET['action']) && $_GET['action'] !== '' && isset($_GET['id']))
	{
		$action = strtolower($_GET['action']);
		if ($action !== 'new')
		{
			//Getting calendar.
			$calendar = $connection->getCalendar([intval($_GET['id'])]);
			if ($calendar === false || empty($calendar))
				exit('No calendar found. :(');
			$calendar = $calendar[0];
		}

		if ($action === 'delete')
		{
			if ($connection->removeCalendar($calendar['id']) === true)
				echo generateCalendarRow($calendar, 'deleted');
		}
		elseif (in_array($action, ['new', 'save'], true))
		{
			if (isset($_POST['name']))
				$calendar['name'] = trim($_POST['name']);

			if (isset($_POST['enable_weather']))
				$calendar['enable_weather'] = 1;
			else
				$calendar['enable_weather'] = 0;

			if (isset($_POST['ics_link']))
				$calendar['ics_link'] = trim($_POST['ics_link']);
			else
				$calendar['ics_link'] = '';

			if ($action === 'new')
			{
				$_GET['id'] = $connection->newCalendar($calendar['name'], $calendar['enable_weather'], $calendar['ics_link']);
				if ($_GET['id'] === false)
					exit('ERROR ADDING NEW CALENDAR!!!');
			}
			elseif ($action === 'save')
			{
				if ($connection->saveCalendar($calendar['id'], $calendar['name'], $calendar['enable_weather'], $calendar['ics_link']) === false)
					exit('ERROR SAVING!!!');
			}
			$calendar = $connection->getCalendar([intval($_GET['id'])]);
			$calendar = $calendar[0]; //Updating calendar from database.
			if ($action === 'new') //Adding new "new" row.
			{
				echo generateCalendarRow($calendar, 'new');
				$action = 'added';
			}
			else
				$action = 'view';
		}
		echo generateCalendarRow($calendar, $action);
	}
	else
	{
		//Getting all calendars.
		$calendars = $connection->getCalendar();
		if ($calendars === false || empty($calendars))
			exit('No calendars found. :(');
		$tableHeaders = '';
		foreach (array_keys($calendars[0]) as $calendarKey)
		{
			$calendarKey = ucwords(str_replace('_', ' ', $calendarKey));
			$tableHeaders .= <<<HTML
				<th>{$calendarKey}</th>
				HTML;
		}

		$tableHeaders .= <<<HTML
			<th>Ics Link</th>
			<th>Action</th>
			HTML;

		$tableRows = generateCalendarRow($calendars[0], 'new');
		foreach ($calendars as $calendarKey => $calendar)
		{
			$tableRows .= generateCalendarRow($calendar, 'view');
		}

		echo <<<HTML
			<h2>Calendar Management</h2>
			<table hx-confirm="Are you sure you want to continue?">
				<thead>
					<tr>
						{$tableHeaders}
					</tr>
				</thead>
				<tbody>
					{$tableRows}
				</tbody>
			</table>
			HTML;
	}
}

if (isHTMX() === false)
{
	echo <<<HTML
		</main>
		HTML;
}

require_once(__DIR__ . '/../templates/footer.php');

function generateCalendarRow(array $calendar, string $type = 'view'): string
{
	if (in_array($type, ['added', 'view'], true))
	{
		if ($type === 'added')
			$class = 'added';
		else
			$class = '';
		$calendar['enable_weather'] = ($calendar['enable_weather'] === 1 ? 'ON' : 'OFF');
		return <<<HTML
			<tr class="{$class}" hx-include="this">
				<td class="center">
					{$calendar['id']}
				</td>
				<td>
					{$calendar['name']}
				</td>
				<td class="center">
					{$calendar['enable_weather']}
				</td>
				<td class="center">
					[HIDDEN]
				</td>
				<td class="center">
					<a href="?action=edit&id={$calendar['id']}" hx-get="?action=edit&id={$calendar['id']}" hx-select="tr" hx-target="closest tr" hx-swap="outerHTML" hx-confirm="unset">Edit</a>
				</td>
			</tr>
			HTML;
	}
	elseif ($type === 'new')
	{
		return <<<HTML
			<tr hx-include="this">
				<td class="center">
					N/A
				</td>
				<td>
					<div class="input-container">
						<input type="text" hx-validate="true" name="name" placeholder="A Friendly Public Calendar Name" maxlength="255" required>
					</div>
				</td>
				<td class="center">
					<input type="checkbox" hx-validate="true" name="enable_weather" checked>
				</td>
				<td>
					<div class="input-container">
						<input type="password" hx-validate="true" name="ics_link" placeholder="[HIDDEN]" required>
					</div>
				</td>
				<td class="center">
					<a href="?action=new" hx-post="?action=new" hx-select="tr" hx-target="closest tr" hx-swap="outerHTML" hx-validate="true">Add</a>
				</td>
			</tr>
			HTML;
	}
	elseif ($type === 'edit')
	{
		$checkedStatus = (boolval($calendar['enable_weather']) === true ? ' checked' : '');
		return <<<HTML
			<tr hx-include="this">
				<td class="center">
					{$calendar['id']}
				</td>
				<td>
					<div class="input-container">
						<input type="text" name="name" value="{$calendar['name']}" placeholder="A Friendly Public Calendar Name" maxlength="255" required>
					</div>
				</td>
				<td class="center">
					<input type="checkbox" name="enable_weather" {$checkedStatus}>
				</td>
				<td>
					<div class="input-container">
						<input type="password" value="" name="ics_link" placeholder="[HIDDEN]">
					</div>
				</td>
				<td class="center">
					<a href="?action=save&id={$calendar['id']}" hx-post="?action=save&id={$calendar['id']}" hx-select="tr" hx-target="closest tr" hx-swap="outerHTML">Save</a> | <a href="?action=delete&id={$calendar['id']}" hx-post="?action=delete&id={$calendar['id']}" hx-select="tr" hx-target="closest tr" hx-swap="outerHTML">Delete</a> | <a href="?action=view&id={$calendar['id']}" hx-get="?action=view&id={$calendar['id']}" hx-select="tr" hx-target="closest tr" hx-swap="outerHTML">Cancel</a>
				</td>
			</tr>
			HTML;
	}
	elseif ($type === 'deleted')
	{
		$calendar['enable_weather'] = ($calendar['enable_weather'] === 1 ? 'ON' : 'OFF');
		return <<<HTML
			<tr class="deleted">
				<td class="center">
					{$calendar['id']}
				</td>
				<td>
					{$calendar['name']}
				</td>
				<td class="center">
					{$calendar['enable_weather']}
				</td>
				<td class="center">
					[HIDDEN]
				</td>
				<td class="center">
					DELETED!
				</td>
			</tr>
			HTML;
	}
	return 'ERROR! THIS SHOULDN\'T BE POSSIBLE!';
}
