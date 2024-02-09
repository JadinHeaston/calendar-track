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
	$icalHandle->disableCharacterReplacement = true;
	$icalHandle->filterDaysBefore = 0;
	$icalHandle->filterDaysAfter = UI_DAY_RANGE;

	//Check for file in with this ID in ./cache/calendars/
	$filename = './cache/calendars/' . $id . '.phpobj';

	//If file exists and is within the timeout range.
	if (file_exists($filename) && time() - filectime($filename) < FSCACHE_ICAL_CACHE_PERIOD)
	{
		$events = unserialize(file_get_contents($filename));
	}
	else //No valid file to use. Download it.
	{
		try
		{
			$icsData = curlContent($icsLink);
			$icalHandle->initString($icsData);
		}
		catch (Exception $error)
		{
			$icalHandle = unserialize(file_get_contents($filename));
		}

		//Check that data was recieved.
		if ($icalHandle !== null && empty($icalHandle) === false)
		{
			$events = $icalHandle->eventsFromInterval(UI_DAY_RANGE . ' days');
			unset($icalHandle);
			file_put_contents($filename, serialize($events));
		}
		else //Bad/No data recieved.
		{
			if (file_exists($filename))
			{
				$events = unserialize(file_get_contents($filename));
			}
			else
			{
				return false;
			}
		}
	}
	return $events;
}

function getCachedICSLastModificationTime(int $id)
{
	clearstatcache();
	$dt = new DateTime();
	return $dt->setTimestamp(filectime('./cache/calendars/' . $id . '.phpobj'));
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

function getWeatherAPIStatus(): bool
{
	$result = json_decode(curlContent('https://api.weather.gov/'));
	if ($result->status === 'OK')
		return true;
	else
		return false;
}

function printCurrentMemory()
{
	return round((memory_get_usage() / 1024) / 1024, 3) . 'MB (' . round((memory_get_peak_usage() / 1024) / 1024, 3) . 'MB)';
}
