<?php

namespace App\Cron\Job;

class PurgeExpiredTokens extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Purge expired API tokens';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		\App\Tools\SessionManager::purgeOld();
		}

	public function willRun() : bool
		{
		$oauthTokenTable = new \App\Table\OauthToken();
		$oauthTokenTable->setWhere(new \PHPFUI\ORM\Condition('', \date('Y-m-d H:i:s'), new \PHPFUI\ORM\Operator\LessThan()));
		$oauthTokenTable->delete();

		return $this->controller->runAt(0, 10);
		}
	}
