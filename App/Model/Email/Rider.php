<?php

namespace App\Model\Email;

class Rider extends \App\Model\EmailData
	{
	public function __construct(\App\Record\Ride $ride = new \App\Record\Ride(), \App\Record\Member $member = new \App\Record\Member())
		{
		$settingTable = new \App\Table\Setting();
		$url = $settingTable->value('homePage');

		$link = ['signUpLink' => "<a href='{$url}/Rides/signedUp/{$ride->rideId}'>{$ride->title}</a>"];

		if ($ride->empty())
			{
			$rideTable = new \App\Table\Ride();
			$rideTable->addOrderBy('rideId', 'desc');
			$rideTable->setLimit(1);
			$ride = $rideTable->getRecordCursor()->current();
			}

		if ($member->empty())
			{
			$memberTable = new \App\Table\Member();
			$memberTable->addOrderBy('memberId', 'desc');
			$memberTable->setLimit(1);
			$member = $memberTable->getRecordCursor()->current();
			}
		$this->fields = \array_merge($ride->toArray(), $member->toArray(), $link);
		}
	}
