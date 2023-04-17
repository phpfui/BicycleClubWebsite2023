<?php

namespace App\Cron\Job;

class PurgePendingMembers extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Purge pending members who have not yet paid.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$memberModel = new \App\Model\Member();
		$memberModel->purgePending();
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(4, 40);
		}
	}
