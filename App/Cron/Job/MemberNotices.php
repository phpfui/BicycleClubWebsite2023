<?php

namespace App\Cron\Job;

class MemberNotices extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Send out member notices';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$memberNoticeTable = new \App\Table\MemberNotice();

		$memberTable = new \App\Table\Member();
		$memberTable->addJoin('membership');

		foreach ($memberNoticeTable->getRecordCursor() as $notice)
			{
			$dayOffsets = \explode(',', $notice->dayOffsets);

			foreach ($dayOffsets as $days)
				{
				$dayInt = (int)$days;

				if ($dayInt == $days)	// eliminates non integers that evaluate to 0
					{
					$date = \App\Tools\Date::todayString($dayInt);
					$endDate = \App\Tools\Date::todayString($dayInt + 1);
					$condition = new \PHPFUI\ORM\Condition($notice->field, $date, new \PHPFUI\ORM\Operator\GreaterThanEqual());
					$condition->and($notice->field, $endDate, new \PHPFUI\ORM\Operator\LessThan());
					$memberTable->setWhere($condition);

					foreach ($memberTable->getRecordCursor() as $member)
						{
						$email = new \App\Model\Email\Notice($notice, new \App\Model\Email\Member($member));
						$email->setToMember($member->toArray());
						$email->bulkSend();
						}
					}
				}
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(9, 30);
		}
	}
