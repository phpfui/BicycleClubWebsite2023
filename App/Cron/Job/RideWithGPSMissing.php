<?php

namespace App\Cron\Job;

class RideWithGPSMissing extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Delete Missing RideWithGPS Routes';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$rwgpsTable = new \App\Table\RWGPS();
		$rideTable = new \App\Table\Ride();
		$missing = [];

		$rides = $rwgpsTable->getMissing();

		foreach ($rides as $rwgps)
			{
			$missing[] = $rwgps->RWGPSId;
			$rwgps->delete();
			}

		$rideTable->deleteRWGPS($missing);
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(4, 55);
		}
	}
