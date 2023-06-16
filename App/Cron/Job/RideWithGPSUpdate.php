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

		$upcoming = $rwgpsTable->getUpcomingRWGPS();

		foreach ($upcoming as $rwgps)
			{
			$updated = $model->scrape($rwgps);

			if ($updated && $updated->RWGPSId)
				{
				$updated->insertOrUpdate();
				}
			}

		$rides = $rwgpsTable->getOldest(20);

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
