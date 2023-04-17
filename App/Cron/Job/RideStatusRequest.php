<?php

namespace App\Cron\Job;

class RideStatusRequest extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Send out ride status reporting emails.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$dates = [];

		foreach ([1, 2, 4, 7, 10, 14] as $days)
			{
			$dates[] = \App\Tools\Date::toString($this->controller->runningAtJD() - $days);
			}
		$this->sendStatusNotice($dates);
		}

	/** @param array<string> $days */
	public function sendStatusNotice(array $days) : void
		{
		$model = new \App\Model\RideStatusRequest();

		foreach (\App\Table\Ride::unreportedRidesOn($days) as $ride)
			{
			$model->send($ride);
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(1, 20);
		}
	}
