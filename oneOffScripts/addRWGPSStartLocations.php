<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

echo "\nAdd start locations to RWGPS routes based on rides\n";

$sql = 'select ride.*
from ride
left join startLocation on ride.startLocationId=startLocation.startLocationId
left join rwgps on ride.RWGPSId=rwgps.RWGPSId
where startLocation.latitude is null and ride.RWGPSId is not null and rwgps.latitude is not null and rwgps.startLocationId is null';
$rides = \PHPFUI\ORM::getArrayCursor($sql, []);
$RWGPSIds = [];

foreach ($rides as $ride)
	{
	$RWGPSId = $ride['RWGPSId'];
	$startLocationId = $ride['startLocationId'];

	if (! isset($RWGPSIds[$RWGPSId]))
		{
		$RWGPSIds[$RWGPSId] = [];
		}

	if (! isset($RWGPSIds[$RWGPSId][$startLocationId]))
		{
		$RWGPSIds[$RWGPSId][$startLocationId] = 0;
		}
	++$RWGPSIds[$RWGPSId][$startLocationId];
	}

$counts = [];
$rideTable = new \App\Table\Ride();

foreach ($RWGPSIds as $RWGPSId => $startLocations)
	{
	$count = \count($startLocations);
	$rwgps = new \App\Record\RWGPS($RWGPSId);

	if (isset($startLocations[702]))
		{
		$rideTable->setWhere(new \PHPFUI\ORM\Condition('RWGPSId', $RWGPSId));
		$rideTable->update(['startLocationId' => 702]);
		$rwgps->startLocationId = 702;
		}
	else
		{
		\arsort($startLocations);
		\reset($startLocations);
		$rwgps->startLocationId = \current($startLocations);
		}
	$rwgps->update();

	foreach ($startLocations as $startLocationId => $count)
		{
		$startLocation = new \App\Record\StartLocation($startLocationId);

		if (! $startLocation->latitude && ! $startLocation->longitude && $rwgps->latitude && $rwgps->longitude)
			{
			$startLocation->latitude = $rwgps->latitude;
			$startLocation->longitude = $rwgps->longitude;
			$startLocation->update();
			}
		}
	}
