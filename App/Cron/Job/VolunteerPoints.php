<?php

namespace App\Cron\Job;

class VolunteerPoints extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Tally Volunteer Points.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$model = new \App\Model\Volunteer();

		// points for rides
		$model->assignRidePoints();

		// points for RWGPS lead
		$model->assignRWGPSPoints();

		// points for volunteers
		$model->assignVolunteerPoints();

		// write out all the points
		$model->saveMemberPoints();
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(2, 10);
		}
	}
