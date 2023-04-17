<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

$routeArray = [3, 3, 3, 3, 3, 3, 3, 3, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 3, 3, 3, 3, 3, 3, 3, 3];
$THNRouteArray = [0, 0, 1, 1, 2, 2, 3, 3, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 3, 3, 3, 3, 3, 3, 3, 3, 3];
$TNROffset = [1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 2, 2, 3, 4, 5];
$ThNROffset = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 2, 3, 4, 4, 5];
$cuesheets = [1419, 1418, 1420, 1421, 330, 1422];
$rwgps = [32967573, 32967639, 32967686, 32967698, 32967711, 32967731];
$elevations = [1200, 1700, 1900, 2500, 2800, 3300];

$tuesdayRides = [23 => 'Whippoorwill East route, not the whole Whippoorwill climb this time.  Will not be a big group, so try your hand at climbing hard on Whippoorwill.',
	32 => 'Route 128 route, all of Whippoorwill and Route 128 for good measure, but not a big group.',
	34 => 'Chestnut Ridge route. Will not be a big group, so try your hand at climbing hard on Rt 172.',
	36 => 'Rt 22 route. Can you survive the climbs on Rt 22 immediately after the long climb up Rt 172?',
	48 => 'Rt 137 route around Bedford, the real deal. Save something for Rt 22 on the way back.',
	55 => 'We ride up to Rt 35 and back on Rt 124. The hardest and best ride of the season.'];
$day = ['Tuesday', 'Thursday'];

\PHPFUI\ORM::execute('delete from ride where memberId=2590 and rideDate>=?', [\App\Tools\Date::todayString()]);

$week = 0;
$page = new \PHPFUI\Page();
$page->addStyleSheet('/css/styles.css');
$table = new \PHPFUI\Table();
$table->setHeaders(['day' => 'Day', 'date' => 'Date', 'start' => 'Start', 'mileage' => 'Mileage', 'sunset' => 'Sunset', 'duration' => 'Duration', 'average' => 'Average', ]);

$today = \App\Tools\Date::todayString();

foreach ($routeArray as $routeKey => $i)
	{
	$date = \gregoriantojd(3, 14, 2023) + $week * 7;

	for ($j = 0; $j < 2; ++$j)
		{
		$route = $i;
		$jd = \jdtounix($date) + 17.5 * 3600;
		$info = \date_sun_info(\jdtounix($date), 41.033, -73.763);
		$sunset = $info['sunset'] - 4 * 3600;
		$count = 0;
		$catid = 13;
		$offset = $TNROffset[$week];

		if (2 == \gmdate('w', \jdtounix($date)))
			{
			$catid = 12;

			if ($route < 3)
				{
				$route = 3;
				}
			}
		else
			{
			$route = $THNRouteArray[$routeKey];
			$offset = $ThNROffset[$week];
			}

		foreach ($tuesdayRides as $key => $value)
			{
			$mileage = $key;
			$desc = $value;

			if ($count++ == $route)
				break;
			}
		$now = \time();
		$jd -= $offset * 900;
		$time = \gmdate('g:ia', $jd);
		$sunsetString = \gmdate('g:ia', $sunset);
		$daylight = \App\Tools\TimeHelper::fromString($sunsetString) - \App\Tools\TimeHelper::fromString($time);
		$daylightString = (int)($daylight / 60) . ':' . \sprintf('%02d', (int)($daylight % 60));
		$desc .= ' Back by dark, but bring a light. Ride leaves promptly. Sunset: ' . $sunsetString;
		$title = $day[$j] . ' Night Training Ride';

		$ride = new \App\Record\Ride();
		$ride->rideDate = \App\Tools\Date::toString($date);

		if ($ride->rideDate < $today)
			{
			continue;
			}
		$ride->rideStatus = 0;
		$ride->mileage = (string)$mileage;
		$ride->startTime = $time;
		$ride->title = $title;
//		$ride->cueSheetId = $cuesheets[$route];
		$ride->description = $desc;
		$ride->elevation = $elevations[$route];
		$ride->memberId = 2590;
		$ride->unaffiliated = 1;
		$ride->dateAdded = \date('Y-m-d H:i:s');
		$ride->paceId = $catid;
		$ride->startLocationId = 26;
		$ride->targetPace = 20.0 - $j;
		$ride->regrouping = 'At lights';
		$ride->RWGPSId = $rwgps[$route];

		$row = [];
		$row['day'] = \App\Tools\Date::format('D', $date);
		$row['date'] = \App\Tools\Date::format('M j', $date);
		$row['mileage'] = $mileage;
		$row['start'] = $time;
		$row['sunset'] = $sunsetString;
		$row['duration'] = $daylightString;
		$hour = $daylight / 60;
		$row['average'] = \number_format($mileage / $hour, 1);
		$rideId = $ride->insert();
		$row['rideId'] = $rideId;
		$table->addRow($row);
		$date += 2;
		}
	$week++;
	}
$page->add($table);
echo $page;
