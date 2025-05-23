<?php

namespace App\Cron\Job;

class RideWithGPSAddClubRides extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Add RideWithGPS Club Rides';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$model = new \App\Model\RideWithGPS();

		$routes = $model->getClubRoutes();

		foreach ($routes as $ride)
			{
			$record = new \App\Record\RWGPS($ride['id']);

			if (! $record->loaded())
				{
				$record = $model->scrape($record, true);

				if ($record)
					{
					$record->club = 1;
					$record->insertOrUpdate();
					}
				else
					{
					break;
					}
				}
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(20, 45);
		}
	}
