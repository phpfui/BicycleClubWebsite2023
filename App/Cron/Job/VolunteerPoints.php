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
		$model->assignRidePoints($parameters['rideDaysOut'] ?? 180);

		// points for RWGPS lead
		$model->assignRWGPSPoints($parameters['RWGPSDaysOut'] ?? 180);

		// points for volunteers
		$model->assignVolunteerPoints($parameters['volunteerDaysOut'] ?? 180);

		// write out all the points
		$model->saveMemberPoints();
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(2, 10);
		}
	}
