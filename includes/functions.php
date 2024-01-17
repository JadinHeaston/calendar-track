<?PHP

function isHTMX()
{
	$headers = getallheaders();
	return ($headers !== false && isset($headers['Hx-Request']) && boolval($headers['Hx-Request']) === true);
}

function getICSEventData(int $id, string $icsLink)
{
	require_once(__DIR__ . '/../vendor/ICal_loader.php');
	$icalHandle = new Ical\ICal();
	$icalHandle->filterDaysBefore = 0;
	$icalHandle->filterDaysAfter = UI_DAY_RANGE;

	//Check for file in with this ID in ./cache/calendars/
	$filename = './cache/calendars/' . $id . '.ics';

	//If file exists and is within the timeout range.
	if (file_exists($filename) && time() - filectime($filename) < FSCACHE_WEATHER_CACHE_PERIOD)
	{
		$icalHandle->initFile($filename);
	}
	else //No valid file to use. Download it.
	{
		$icsData = curlContent($icsLink);
		file_put_contents($filename, $icsData);
		$icalHandle->initString($icsData);
	}

	return $icalHandle->eventsFromInterval(UI_DAY_RANGE . ' days');
}

function getCachedICSLastModificationTime(int $id)
{
	clearstatcache();
	$dt = new DateTime();
	return $dt->setTimestamp(filectime('./cache/calendars/' . $id . '.ics'));
}

function curlContent(string $url)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_USERAGENT, MISC_USER_AGENT);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}

function printCurrentMemory()
{
	return round((memory_get_usage() / 1024) / 1024, 3) . 'MB (' . round((memory_get_peak_usage() / 1024) / 1024, 3) . 'MB)';
}
