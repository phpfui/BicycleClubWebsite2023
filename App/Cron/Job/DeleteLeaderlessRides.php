<?php

namespace App\Cron\Job;

class DeleteLeaderlessRides extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Delete Leaderless Rides after Ride date.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$today = $this->controller->runningAtJD();
		$rideTable = new \App\Table\Ride();

		$rides = $rideTable->getLeaderlessRides($today + 1);

		foreach ($rides as $ride)
			{
			$ride->delete();
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(0, 5);
		}
	}
