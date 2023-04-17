<?php

namespace App\Cron\Job;

class NewMemberEmail extends \App\Cron\MemberMailer
	{
	public function getDescription() : string
		{
		return 'Send the New Member email out to new members.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$membershipTable = new \App\Table\Membership();
		$memberships = $membershipTable->getNewMemberships();

		if (\count($memberships))
			{
			$settingTable = new \App\Table\Setting();
			$title = 'Welcome to the ' . $settingTable->value('clubName');
			$message = $settingTable->value('newMember');
			$memberPicker = new \App\Model\MemberPicker('Membership Chair');
			$membershipChair = $memberPicker->getMember();
			$this->bulkMailMembers($memberships, $title, $message, $membershipChair);
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(4, 30);
		}
	}
