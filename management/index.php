<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() !== PHP_SESSION_ACTIVE)
	session_start();
require_once(__DIR__ . '/../includes/loader.php');

require_once(__DIR__ . '/../templates/header.php');

if (empty(CALENDAR_MANAGEMENT_PASSWORD_HASH))
{
	echo 'Management disabled. Please set a management password.';
	die(1);
}

echo <<<HTML
	<main id="management">
	HTML;

if ($_SESSION['authenticated'] !== true)
{
	//If they are posting a password, check that against
	if (isset($_POST['calendar_edit_password']))
	{
		if (hash(CALENDAR_MANAGEMENT_HASH_ALGORITHM, $_POST['calendar_edit_password']) === CALENDAR_MANAGEMENT_PASSWORD_HASH)
			$_SESSION['authenticated'] = true;
		else
		{
			echo <<<HTML
				Incorrect Password.
				HTML;
		}
	}

	//If they are still not authenticated...
	if ($_SESSION['authenticated'] !== true)
	{
		//Display login page.
		echo <<<HTML
		<form class="flex flex-col items-center justify-center h-full rounded-lg bg-gray-100 dark:bg-gray-900" method="post" action="" hx-post="" hx-select="main" hx-target="main" hx-swap="outerHTML">
			<label class="text-2xl" for="calendar_edit_password">Calendar Edit Password</label>
			<input type="password" id="calendar_edit_password" name="calendar_edit_password" placeholder="Password" class="text-xl m-8 bg-gray-200 dark:bg-gray-800">
			<input type="submit" class="w-full cursor-pointer delay-75 font-medium rounded-lg px-5 py-2.5 text-center bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 hover:dark:bg-gray-700">
		</form>
		HTML;
	}
}
elseif ($_SESSION['authenticated'] === true)
{
	if (isset($_GET['id']) === false)
		$_GET['id'] = [];

	if (isset($_GET['action']) && $_GET['action'] !== '' && isset($_GET['id']))
	{
		//Getting calendar.
		$calendar = $connection->getCalendar([intval($_GET['id'])]);
		if ($calendar === false || empty($calendar))
		{
			echo 'No calendar found. :(';
			exit();
		}
		$calendar = $calendar[0];

		echo 'ayo';
		if (strtolower($_GET['action']) === 'view')
		{
			echo <<<HTML
				<tr>
					<td>{$calendar['name']}</td>
					<td>{$calendar['enable_weather']}</td>
					<td>[HIDDEN]</td>
					<td class="text-center"><a href="?action=edit&id={$calendar['id']}" hx-get="?action=edit&id={$calendar['id']}" hx-select="tr" hx-target="closest tr" hx-swap="outerHTML">Edit</a></td>
				</tr>
				HTML;
		}
		elseif (strtolower($_GET['action']) === 'edit')
		{
			echo <<<HTML
				<tr>
					<td><input type="text" value="{$calendar['name']}"></td>
					<td><input type="checkbox" value=""></td>
					<td><input type="text" value="" placeholder="{REDACTED}"></td>
					<td class="text-center"><a href="?action=save&id={$calendar['id']}" hx-get="?action=save&id={$calendar['id']}" hx-select="tr" hx-target="closest tr" hx-swap="outerHTML">Save</a> | <a href="?action=view&id={$calendar['id']}" hx-get="?action=view&id={$calendar['id']}" hx-select="tr" hx-target="closest tr" hx-swap="outerHTML">Cancel</a></td>
				</tr>
				HTML;
		}
		elseif (strtolower($_GET['action']) === 'save')
		{
		}
	}
	else
	{
		//Getting all calendars.
		$calendars = $connection->getCalendar();
		if ($calendars === false || empty($calendars))
		{
			echo 'No calendars found. :(';
			exit();
		}
		$tableHeaders = '';
		foreach (array_keys($calendars[0]) as $calendarKey)
		{
			if ($calendarKey === 'id')
				continue;
			$calendarKey = ucwords(str_replace('_', ' ', $calendarKey));
			$tableHeaders .= <<<HTML
				<th>{$calendarKey}</th>
				HTML;
		}

		$tableHeaders .= <<<HTML
			<th>Ics Link</th>
			<th>Action</th>
			HTML;


		$tableRows = '';

		foreach ($calendars as $calendarKey => $calendar)
		{
			if ($calendarKey === 'id')
				continue;
			$tableRows .= <<<HTML
				<tr>
					<td>{$calendar['name']}</td>
					<td>{$calendar['enable_weather']}</td>
					<td>[HIDDEN]</td>
					<td><a href="?action=edit&id={$calendar['id']}" hx-get="?action=edit&id={$calendar['id']}" hx-select="tr" hx-target="closest tr" hx-swap="outerHTML">Edit</a></td>
				</tr>
				HTML;
		}

		echo <<<HTML
			<h2>Calendar Management</h2>
			<table>
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


echo <<<HTML
	</main>
	HTML;

require_once(__DIR__ . '/../templates/footer.php');
