<?php

namespace App\Cron\Job;

class NewRideNotices extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Email ride notices to any newly posted rides.';
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
		$endTime = \mktime($hour, $minute, 0, $m, $d, $y) - 1800;

		$model = new \App\Model\Ride();
		$rideTable = new \App\Table\Ride();
		$rides = $rideTable->getNewlyAddedUpcomingRides(\date('Y-m-d H:i:s', $endTime - 3600), \date('Y-m-d H:i:s', $endTime));
		$todayString = \App\Tools\Date::toString($today);

		foreach ($rides as $ride)
			{
			if ($ride->rideDate >= $todayString && \App\Enum\Ride\Status::NOT_YET == $ride->rideStatus)
				{
				$model->emailNewRideNotice($ride);
				}
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runHourly();
		}
	}
