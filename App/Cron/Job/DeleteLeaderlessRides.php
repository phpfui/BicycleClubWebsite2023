<?php

namespace App\Cron\Job;

class DeleteLeaderlessRides extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Delete Leaderless Rides after Ride date.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$today = \App\Tools\Date::toString($this->controller->runningAtJD());
		$rideTable = new \App\Table\Ride();
		$condition = new \PHPFUI\ORM\Condition('memberId', 0);
		$condition->or('memberId', null, new \PHPFUI\ORM\Operator\IsNull());
		$fullCondition = new \PHPFUI\ORM\Condition('rideDate', $today, new \PHPFUI\ORM\Operator\LessThan());
		$fullCondition->and($condition);
		$rideTable->setWhere($condition)->delete();
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(0, 5);
		}
	}
