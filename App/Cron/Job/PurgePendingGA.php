<?php

namespace App\Cron\Job;

class PurgePendingGA extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Purge General Admission pending dup registrations from current events.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$gariderTable = new \App\Table\GaRider();
		$gaEventTable = new \App\Table\GaEvent();
		$events = $gaEventTable->getCurrentEvents();

		foreach ($events as $event)
			{
			$gariderTable->purgePendingDupes($event);
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(5, 30);
		}
	}
