<?php

namespace App\Cron\Job;

class RenewalEmail extends \App\Cron\MemberMailer
	{
	public function getDescription() : string
		{
		return 'Send renewal email reminder.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$membershipTable = new \App\Table\Membership();
		$month = $this->controller->runningAtMonth();
		$year = $this->controller->runningAtYear();
		$start = \App\Tools\Date::makeString($year, $month, 1);

		if (++$month > 12)
			{
			++$year;
			$month = 1;
			}
		$end = \App\Tools\Date::toString(\App\Tools\Date::make($year, $month, 1) - 1);
		$memberships = $membershipTable->getExpiringMemberships($start, $end);

		if (\count($memberships))
			{
			$settingTable = new \App\Table\Setting();
			$title = 'Your ' . $settingTable->value('clubName') . ' membership expires at the end of this month.';
			$message = $settingTable->value('expirngMsg');
			$memberPicker = new \App\Model\MemberPicker('Membership Chair');
			$membershipChair = $memberPicker->getMember();
			$this->bulkMailMembers($memberships, $title, $message, $membershipChair);
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(3, 15) && ($this->controller->runDayOfMonth(1) || $this->controller->runDayOfMonth(15));
		}
	}
