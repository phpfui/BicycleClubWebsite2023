<?php

namespace App\Cron\Job;

class NewMemberFollowupEmail extends \App\Cron\MemberMailer
	{
	public function getDescription() : string
		{
		return 'Send the New Member Followup email to new members.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$settingTable = new \App\Table\Setting();
		$daysBack = $settingTable->value('NMEMdays');
		$membershipTable = new \App\Table\Membership();
		$memberships = $membershipTable->getNewMemberships((int)$daysBack);

		if (\count($memberships))
			{
			$title = $settingTable->value('NMEMtitle');
			$message = $settingTable->value('NMEMbody');
			$memberPicker = new \App\Model\MemberPicker('Membership Chair');
			$membershipChair = $memberPicker->getMember();
			$this->bulkMailMembers($memberships, $title, $message, $membershipChair);
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(4, 35);
		}
	}
