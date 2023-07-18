<?php

namespace App\Model;

class RideStatusRequest
	{
	private readonly string $abbrev;

	private readonly string $request;

	/** @var array<string,mixed> */
	private readonly array $sender;

	public function __construct()
		{
		$settingTable = new \App\Table\Setting();
		$this->abbrev = $settingTable->value('clubAbbrev');
		$this->request = $settingTable->value('requestSta');
		$memberPicker = new \App\Model\MemberPicker('Rides Chair');
		$this->sender = $memberPicker->getMember();
		}

	public function send(\App\Record\Ride $ride) : void
		{
		if ($ride->unaffiliated)
			{
			return; // we don't send unaffiliated ride status requests
			}
		$title = $this->abbrev . ' Request for Ride Status for your ride on ' . \App\Tools\Date::formatString('l, F j', $ride->rideDate);
		$message = "Dear {$ride->member->fullName()},\n\n{$this->request}";
		$message = \App\Tools\TextHelper::processText($message, $ride->toArray());
		$email = new \App\Tools\EMail();
		$email->setHtml();
		$email->setSubject($title);
		$email->setBody($message);
		$email->setFromMember($this->sender);
		$email->addBCCMember($this->sender);
		$leader = $ride->member;

		if ($leader->loaded())
			{
			$email->addToMember($leader->toArray());
			$email->bulkSend();
			}
		}
	}
