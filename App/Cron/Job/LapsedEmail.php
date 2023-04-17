<?php

namespace App\Cron\Job;

class LapsedEmail extends \App\Cron\MemberMailer
	{
	public function getDescription() : string
		{
		return 'Send out Lapsed email notifications to former members.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$membershipsTable = new \App\Table\Membership();
		$month = $this->controller->runningAtMonth();
		$year = $this->controller->runningAtYear();
		$end = \App\Tools\Date::toString(\App\Tools\Date::make($year, $month, 1) - 1);

		if (--$month < 1)
			{
			--$year;
			$month = 12;
			}
		$start = \App\Tools\Date::makeString($year, $month, 1);
		$memberships = $membershipsTable->getExpiringMemberships($start, $end);

		if (\count($memberships))
			{
			$settingTable = new \App\Table\Setting();
			$title = 'Your ' . $settingTable->value('clubName') . ' membership has lapsed. Renew today!';
			$message = $settingTable->value('expireMsg');
			$memberPicker = new \App\Model\MemberPicker('Membership Chair');
			$membershipChair = $memberPicker->getMember();
			$this->bulkMailMembers($memberships, $title, $message, $membershipChair);
			}
		}

	public function willRun() : bool
		{
		return ($this->controller->runDayOfMonth(1) || $this->controller->runDayOfMonth(15)) && $this->controller->runAt(3, 30);
		}
	}
