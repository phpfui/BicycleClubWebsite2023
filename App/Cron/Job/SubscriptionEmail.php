<?php

namespace App\Cron\Job;

class SubscriptionEmail extends \App\Cron\MemberMailer
	{
	/** @var array<string, mixed> */
	private readonly array $membershipChair;

	private readonly \App\Table\Membership $membershipsTable;

	private readonly \App\Table\Setting $settingTable;

	public function __construct(\App\Cron\Controller $controller)
		{
		parent::__construct($controller);
		$this->membershipsTable = new \App\Table\Membership();
		$memberPicker = new \App\Model\MemberPicker('Membership Chair');
		$this->membershipChair = $memberPicker->getMember();
		$this->settingTable = new \App\Table\Setting();
		}

	public function getDescription() : string
		{
		return 'Send out subscription renewing email reminder.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$this->emailSubscriptions(28);
		$this->emailSubscriptions(14);
		$this->emailSubscriptions(2);
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(4, 50);
		}

	private function emailSubscriptions(int $days) : void
		{
		$date = $this->controller->runningAtJD() + $days;
		$memberships = $this->membershipsTable->getRenewingMemberships(\App\Tools\Date::toString($date));

		if (\count($memberships))
			{
			$title = 'Your ' . $this->settingTable->value('clubName') . ' subscription will renew soon.';
			$message = $this->settingTable->value('subscriptionMsg');
			$message = \str_replace('~renewDays~', (string)$days, $message);
			$message = \str_replace('~renewDate~', \App\Tools\Date::toString($date, 'l F j, Y'), $message);
			$this->bulkMailMembers($memberships, $title, $message, $this->membershipChair);
			}
		}
	}
