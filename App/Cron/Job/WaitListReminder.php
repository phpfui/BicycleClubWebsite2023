<?php

namespace App\Cron\Job;

class WaitListReminder extends \App\Cron\MemberMailer
	{
	public function getDescription() : string
		{
		return 'Email wait list reminder for waitlisted rides.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$settingTable = new \App\Table\Setting();
		$title = $settingTable->value('waitListEmailTitle');

		if (empty($title))
			{
			return;
			}

		$tomorrow = \App\Tools\Date::toString($this->controller->runningAtJD() + 1);
		$minute = $this->controller->runningAtMinute();
		$hour = $this->controller->runningAtHour();
		$endTime = $hour * 60 + $minute;
		$startTime = $endTime - 60;

		$rideTable = new \App\Table\Ride();
		$condition = new \PHPFUI\ORM\Condition('rideDate', $tomorrow);
		$condition->and('pending', 0);
		$rideTable->setWhere($condition);
		$rideSignupTable = new \App\Table\RideSignup();

		$title = $settingTable->value('clubAbbrev') . ' ' . $title;
		$website = $this->controller->getSchemeHost();
		$message = $settingTable->value('waitListEmail');

		foreach ($rideTable->getRecordCursor() as $ride)
			{
			$rideTime = \App\Tools\TimeHelper::fromString($ride->startTime);

			if ($rideTime >= $startTime && $rideTime < $endTime)
				{
				$riders = $rideSignupTable->getRidersForStatus($ride, \App\Enum\RideSignup\Status::WAIT_LIST);

				if (\count($riders))
					{
					$members = $rideSignupTable->getCommittedRiders($ride);
					$body = $message . '<br><br>';
					$button = new \PHPFUI\EMailButton('Edit Your Status', $website . '/Rides/signedUp/' . $ride->rideId);
					$body .= $button;

					$this->bulkMailMembers($members, $title, $body, $ride->member->toArray());
					}
				}
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runHourly();
		}
	}
