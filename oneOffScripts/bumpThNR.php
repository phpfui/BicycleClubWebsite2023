<?php

include '../common.php';

$rideTable = new \App\Table\Ride();

$condition = new \PHPFUI\ORM\Condition('title', 'Thursday Night Training Ride');
$condition->and('rideDate', \App\Tools\Date::todayString(-7), new \PHPFUI\ORM\Operator\GreaterThanEqual());
$rideTable->setWhere($condition);

$lastRide = null;

foreach ($rideTable->getRecordCursor() as $ride)
	{
	if (null === $lastRide)
		{
		$lastRide = clone $ride;
		$lastRWGPSId = new \App\Table\RideRWGPS()->setWhere(new \PHPFUI\ORM\Condition('rideId', $lastRide->rideId))->getRecordCursor()->current()->RWGPSId;

		continue;
		}
	$originalRide = clone $ride;

	$ride->mileage = $lastRide->mileage;
	$ride->description = $lastRide->description;
	$currentRWGPS = new \App\Table\RideRWGPS()->setWhere(new \PHPFUI\ORM\Condition('rideId', $ride->rideId))->getRecordCursor()->current();
	$currentRWGPS->delete();
	$nextRWGPSId = $currentRWGPS->RWGPSId;
	$currentRWGPS->RWGPSId = $lastRWGPSId;
	$currentRWGPS->insert();
	$ride->update();

	$lastRide = clone $originalRide;
	$lastRWGPSId = $nextRWGPSId;

	if (48 == $lastRide->mileage)
		{
		break;
		}
	}
