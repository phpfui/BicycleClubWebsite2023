<?php

namespace App\Cron\Job;

class MatchStartLocationRWGPS extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Assign start locations GPS coordinates and to RWGPS routes based on rides';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		try
			{
			$rideTable = new \App\Table\Ride();
			$rideTable->addJoin('startLocation');
			$rideTable->addJoin('RWGPS');
			$where = new \PHPFUI\ORM\Condition('startLocation.latitude', operator:new \PHPFUI\ORM\Operator\IsNull());
			$where->and('ride.RWGPSId', operator:new \PHPFUI\ORM\Operator\IsNotNull());
			$where->and('RWGPS.latitude', operator:new \PHPFUI\ORM\Operator\IsNotNull());
			$where->and('RWGPS.startLocationId', operator:new \PHPFUI\ORM\Operator\IsNull());

			$RWGPSIds = [];

			foreach ($rideTable->getRecordCursor() as $ride)
				{
				$RWGPSId = $ride->RWGPSId;
				$startLocationId = $ride->startLocationId;

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

			foreach ($RWGPSIds as $RWGPSId => $startLocations)
				{
				$rwgps = new \App\Record\RWGPS($RWGPSId);
				\arsort($startLocations);
				\reset($startLocations);
				$rwgps->startLocationId = \key($startLocations);
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
			}
		catch (\Exception $e)
			{
			$this->controller->log_exception($e->getMessage());
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(3, 50);
		}
	}
