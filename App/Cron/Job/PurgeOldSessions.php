<?php

namespace App\Cron\Job;

class PurgeOldSessions extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Purge old session files.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		\App\Tools\SessionManager::purgeOld();
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(3, 30);
		}
	}
