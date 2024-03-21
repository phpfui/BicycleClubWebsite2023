<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

echo "\nGet all cue sheets with RWGPS routes\n";

$sql = 'select c.mileage,c.startLocationId,sl.name startLocation,v.link RWGPSId
	from cuesheetversion v
	left join cuesheet c on c.cueSheetId=v.cueSheetVersionId
	left join startlocation sl on sl.startLocationId=c.startLocationId
	where v.link like "%ridewithgps.com%"';
$rides = \PHPFUI\ORM::getArrayCursor($sql, []);
$csvWriter = new \App\Tools\CSV\FileWriter('rideWithGPS_Cues.csv', false);
$first = true;
$RWGPSIds = [];

foreach ($rides as $ride)
	{
	if ($first)
		{
		$first = false;
		$csvWriter->outputRow(\array_keys($ride));
		}
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
