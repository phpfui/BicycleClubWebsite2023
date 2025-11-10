<?php

namespace App\Cron\Job;

class RideNotices extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Send out journal of ride notices.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$today = $this->controller->runningAtJD();
		$model = new \App\Model\Ride();
		$todayString = \App\Tools\Date::toString($today);
		$nowString = \date('h:i:00');
		$advanceRides = \App\Table\Ride::getDateRange($today, $today + 6);

		$rideDescriptions = [];

		foreach ($advanceRides as $ride)
			{
			if (0 == $ride->pending && $ride->rideStatus == \App\Enum\Ride\Status::NOT_YET)
				{
				$rideDescriptions[$ride->rideId] = $model->getRideNoticeBody($ride);
				}
			}

		$rideChair = $model->getRidesChair();
		$abbrev = $this->controller->getSettingTable()->value('clubAbbrev');
		$website = $this->controller->getSchemeHost();
		$memberTable = new \App\Table\Member();
		$members = $memberTable->getJournalRideInterests();
		$paceTable = new \App\Table\Pace();
		$memberRides = [];

		foreach ($members as $member)
			{
			if (! isset($memberRides[$member['memberId']]))
				{
				$memberRides[$member['memberId']] = ['member' => $member, 'rides' => []];
				}

			foreach ($advanceRides as $ride)
				{
				if ($ride->pending)
					{
					continue;
					}

				if ($ride->rideDate == $todayString && $ride->startTime <= $nowString)
					{
					continue;
					}
				$daysOut = \App\Tools\Date::fromString($ride->rideDate) - $today;

				if ($daysOut >= 0 && $member['rideJournal'] >= $daysOut && $paceTable->getCategoryIdFromPaceId($ride->paceId) == $member['categoryId'])
					{
					$memberRides[$member['memberId']]['rides'][] = $ride->rideId;
					}
				}
			}

		foreach ($memberRides as $pair)
			{
			if ($pair['rides'])
				{
				$member = $pair['member'];
				$email = new \App\Tools\EMail();
				$email->addToMember($member->toArray());
				$email->setSubject($abbrev . ' Ride Notification Journal');
				$body = "Dear {$member['firstName']} {$member['lastName']},<p>Here are your upcoming rides per your request:</p><hr>";

				foreach ($pair['rides'] as $rideId)
					{
					$body .= $rideDescriptions[$rideId];
					$body .= '<hr>';
					}
				$body .= "<a href='{$website}/Membership/myNotifications'>Change your notification settings</a>.";
				$email->setBody($body);
				$email->setHTML();
				$email->setFromMember($rideChair);
				$email->bulkSend();
				}
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(2, 45);
		}
	}
