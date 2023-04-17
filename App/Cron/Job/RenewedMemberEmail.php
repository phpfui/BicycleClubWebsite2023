<?php

namespace App\Cron\Job;

class RenewedMemberEmail extends \App\Cron\MemberMailer
	{
	public function getDescription() : string
		{
		return 'Send renewed thank you email';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$membershipTable = new \App\Table\Membership();
		$memberships = $membershipTable->getRenewedMemberships(1);

		if (\count($memberships))
			{
			$settingTable = new \App\Table\Setting();
			$title = 'Thanks for renewing your ' . $settingTable->value('clubName') . ' membership';
			$message = $settingTable->value('renewedMsg');
			$memberPicker = new \App\Model\MemberPicker('Membership Chair');
			$membershipChair = $memberPicker->getMember();
			$this->bulkMailMembers($memberships, $title, $message, $membershipChair);
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(3, 30);
		}
	}
