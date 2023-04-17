<?php

namespace App\Cron\Job;

class RideWithGPSClubRides extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Update RideWithGPS Club Rides';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$model = new \App\Model\RideWithGPS();
		$rwgpsTable = new \App\Table\RWGPS();

		$rides = $model->getClubRoutes();
		$rwgpsTable->setClubRides($rides);
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(20, 45);
		}
}
