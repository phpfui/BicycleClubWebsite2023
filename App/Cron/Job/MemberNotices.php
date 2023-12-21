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
		$whereCondition = new \PHPFUI\ORM\Condition('summary', 0, new \PHPFUI\ORM\Operator\GreaterThan());
		$memberNoticeTable->setWhere($whereCondition);

		$memberTable = new \App\Table\Member();
		$memberTable->addJoin('membership');

		foreach ($memberNoticeTable->getRecordCursor() as $notice)
			{
			$dayOffsets = \explode(',', $notice->dayOffsets);

			$summaryTable = new \PHPFUI\Table();
			$summaryTable->setHeaders(['Name', 'email', 'Date']);

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

					foreach ($memberTable->getDataObjectCursor() as $member)
						{
						if ($member->emailAnnouncements || $notice->overridePreferences)
							{
							if ($notice->summary < 3)
								{
								$email = new \App\Model\Email\Notice($notice, new \App\Model\Email\Member(new \App\Record\Member($member)));
								$email->setToMember($member->toArray());
								$email->bulkSend();
								}

							if ($notice->summary > 1)
								{
								$memberRecord = new \App\Record\Member($member);
								$summaryTable->addRow(['Name' => $memberRecord->fullName(), 'email' => $member->email, 'Date' => $member[$notice->field]]);
								}
							}
						}
					}
				}

			if (\count($summaryTable))
				{
				$summaryEmail = new \App\Tools\EMail();
				$summaryEmail->addToMember($notice->member->toArray());
				$summaryEmail->setBody($summaryTable);
				$summaryEmail->setSubject("Member Notification Summary for: {$notice->title}");
				$summaryEmail->setHtml();
				$summaryEmail->send();
				}
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(9, 30);
		}
	}
