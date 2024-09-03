<?php

namespace App\Cron\Job;

class RideStatusRequestHourly extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Send out ride status reporting email shortly after ride start time.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$model = new \App\Model\RideStatusRequest();
		$hour = $this->controller->runningAtHour() + $model->getHourOffset();

		foreach (\App\Table\Ride::unreportedRidesOn([\App\Tools\Date::toString($this->controller->runningAtJD())]) as $ride)
			{
			$time = \App\Tools\TimeHelper::fromString($ride->startTime);
			$rideHour = (int)($time / 60);

			if ($hour == $rideHour)
				{
				$model->send($ride);
				}
			}
		}

	public function willRun() : bool
		{
		return 55 == $this->controller->runningAtMinute();
		}
	}
