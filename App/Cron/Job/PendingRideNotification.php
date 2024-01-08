<?php

namespace App\Cron\Job;

class PendingRideNotification extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Email ride coordinators of any newly pending rides.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$today = $this->controller->runningAtJD();
		$minute = $this->controller->runningAtMinute();
		$hour = $this->controller->runningAtHour();
		$y = (int)\App\Tools\Date::year($today);
		$m = (int)\App\Tools\Date::month($today);
		$d = (int)\App\Tools\Date::day($today);
		$endTime = \mktime($hour, $minute, 0, $m, $d, $y);
		$startTime = \date('Y-m-d H:i:s', $endTime - 3600);

		$model = new \App\Model\Ride();
		$rideTable = new \App\Table\Ride();
		$rides = $rideTable->getNewlyAddedUpcomingRides($startTime, pending:1);
		$todayString = \App\Tools\Date::toString($today);

		foreach ($rides as $ride)
			{
			if ($ride->rideDate >= $todayString)
				{
				$model->emailPendingRideNotice($ride);
				}
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runHourly();
		}
	}
