<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

$sql = 'select r.mileage,r.startLocationNumber,sl.name startLocation,r.description RWGPSId,p.pace from rides r left join startlocations sl on sl.id=r.startLocationNumber left join paces p on p.id=r.categoryId where r.description like "%ridewithgps.com%"';
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

$sql = 'select c.mileage,c.startLocation startLocationNumber,sl.name startLocation,v.link RWGPSId from cuesheetversion v left join cuesheets c on c.id=v.id left join startlocations sl on sl.id=c.startLocation where v.link like "%ridewithgps.com%"';
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
