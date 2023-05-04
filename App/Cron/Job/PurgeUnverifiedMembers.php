<?php

namespace App\Cron\Job;

class PurgeUnverifiedMembers extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Purge members who have not verified their email address';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$memberTable = new \App\Table\Member();
		$condition = new \PHPFUI\ORM\Condition('verifiedEmail', 1, new \PHPFUI\ORM\Operator\LessThanEqual());
		$condition->and('lastLogin', \App\Tools\Date::todayString(-3), new \PHPFUI\ORM\Operator\LessThanEqual());
		$memberTable->setWhere($condition);

		foreach ($memberTable->getRecordCursor() as $member)
			{
			$member->membership->delete();
			$member->delete();
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(4, 10);
		}
	}
