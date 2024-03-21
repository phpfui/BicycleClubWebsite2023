<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

echo "\nGet all rides with ridewithgps.com in description (no links, as they are not allowed)\n";

$sql = 'select r.rideDate,r.rideId,r.mileage,r.startLocationId,sl.name startLocation,r.description RWGPSId,p.pace
from ride r
left join startlocation sl on sl.startLocationId=r.startLocationId
left join pace p on p.paceId=r.paceId
where r.description like "%ridewithgps.com%"';
$rides = \PHPFUI\ORM::getArrayCursor($sql, []);
$csvWriter = new \App\Tools\CSVWriter('rideWithGPS_Ids.csv');
$first = true;
$RWGPSIds = [];

foreach ($rides as $ride)
	{
	if ($first)
		{
		$first = false;
		$csvWriter->outputRow(\array_keys($ride));
		}
	$desc = \strtolower($ride['RWGPSId']);
	$linkPos = \strpos($desc, 'ridewithgps');
	$linkPosSpace = \strpos($desc, ' ', $linkPos);
	$link = \substr($desc, $linkPos, $linkPosSpace - $linkPos);
	$ids = \explode('/', $link);

	foreach ($ids as $id)
		{
		$id = (int)$id;

		if ($id)
			{
			if (! isset($RWGPSIds[$id]))
				{
				$ride['RWGPSId'] = $RWGPSIds[$id] = $id;
				$csvWriter->outputRow($ride);

				break;
				}
			}
		}
	}

$sql = 'select c.mileage,c.startLocationId,sl.name startLocation,v.link RWGPSId
	from cuesheetversion v
	left join cuesheet c on c.cueSheetId=v.cueSheetId
	left join startlocation sl on sl.startLocationId=c.startLocationId
	where v.link like "%ridewithgps.com%"';
$rides = \PHPFUI\ORM::getArrayCursor($sql, []);

foreach ($rides as $ride)
	{
	$ids = \explode('/', $ride['RWGPSId']);

	foreach ($ids as $id)
		{
		$id = (int)$id;

		if ($id)
			{
			if (! isset($RWGPSIds[$id]))
				{
				$ride['RWGPSId'] = $RWGPSIds[$id] = $id;
				$csvWriter->outputRow($ride);

				break;
				}
			}
		}
	}
