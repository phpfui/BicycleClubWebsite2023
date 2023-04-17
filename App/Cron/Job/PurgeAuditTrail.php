<?php

namespace App\Cron\Job;

class PurgeAuditTrail extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Purges the audit trail of anything over 31 days old.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		\App\Table\AuditTrail::purge($this->controller->getStartTime() - 31 * 86400);
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(2, 0);
		}
	}
