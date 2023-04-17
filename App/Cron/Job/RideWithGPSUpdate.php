<?php

namespace App\Cron\Job;

class RideWithGPSUpdate extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Update RideWithGPS info';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$model = new \App\Model\RideWithGPS();
		$rwgpsTable = new \App\Table\RWGPS();

		$count = 2;

		$upcoming = $rwgpsTable->getUpcomingEmptyRWGPS();

		foreach ($upcoming as $rwgps)
			{
			if (! $rwgps->RWGPSId)
				{
				$rideTable = new \App\Table\Ride();
				$rideTable->changeRWGPSId(0, null);

				continue;
				}
			$updated = $model->scrape($rwgps);

			if ($updated && $updated->RWGPSId)
				{
				$updated->insertOrUpdate();
				}

			if (--$count <= 0)
				{
				return;
				}
			}

		$rides = $rwgpsTable->getOldest($count);

		foreach ($rides as $rwgps)
			{
			$updated = $model->scrape($rwgps);

			if ($updated && $updated->RWGPSId)
				{
				$updated->update();
				}
			}
		}

	public function willRun() : bool
		{
		return true;
		}
}
