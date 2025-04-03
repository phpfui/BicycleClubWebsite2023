<?php

namespace App\Cron\Job;

class RideWithGPSRemoveLapsed extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Remove lapsed members from RWGPS Club account';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$memberTable = new \App\Table\Member();
		$memberTable->addJoin('membership');
		$memberTable->setWhere(new \PHPFUI\ORM\Condition('expires', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\GreaterThanEqual()));

		$rwgpsModel = new \App\Model\RideWithGPS();

		if (! $rwgpsModel->getAuthToken())
			{
			return;
			}

		$clubMembers = $rwgpsModel->getClubMembers();

		if (empty($clubMembers))
			{
			return;
			}

		foreach ($memberTable->getRecordCursor() as $member)
			{
			if (\array_key_exists($member->email, $clubMembers))
				{
				if (! $clubMembers[$member->email]['active'])
					{
					if (! $rwgpsModel->updateMember($clubMembers[$member->email], active:true))
						{
						return;
						}
					}
				unset($clubMembers[$member->email]);
				}
			}

		foreach ($clubMembers as $clubMember)
			{
			if (! $rwgpsModel->updateMember($clubMember))
				{
				break;
				}
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(5, 20);
		}
	}
