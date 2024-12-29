<?php

namespace App\Model;

class AccidentReport
	{
	public static function report(\App\Record\Ride $ride) : void
		{
		$email = new \App\Tools\EMail();
		$test = false;

		if ($ride->loaded())
			{
			if ($ride->accident > 0)  // already reported
				{
				return;
				}
			}
		else
			{
			$rideTable = new \App\Table\Ride();
			$ride = $rideTable->getNewest();
			$test = true;
			}
		$settings = new \App\Table\Setting();
		$paceTable = new \App\Table\Pace();
		$selectedFile = $settings->value(\App\View\AccidentReport::FILE);
		$email->setHtml();
		$leader = $ride->member;

		if (! $leader->loaded())
			{
			return;
			}
		$topic = 'Crash Report Request for ' . $ride->title . ' ride on ' . \App\Tools\Date::formatString('l, F j', $ride->rideDate) . ' at ' . \App\Tools\TimeHelper::toSmallTime($ride->startTime);

		if ($test)
			{
			$topic = 'TEST: ' . $topic;
			}
		$email->setSubject($topic);
		$location = new \App\Record\StartLocation((int)$ride->startLocationId);
		$leaderLine = '<p>You are receiving this email since you indicated there was an accident on the following ride:<P>';
		$detail = $ride->title . '<br>' .
				\App\Tools\Date::formatString('l, F j', $ride->rideDate) . ' at ' . \App\Tools\TimeHelper::toSmallTime($ride->startTime) . '<br>' .
				$ride->mileage . ' miles at a ' . $paceTable->getPace($ride->paceId) . ' pace<br>' .
				'Starting from ' . $location->name . ',<p>' . $ride->description . '<p>' .
				'Leader: ' . $leader->fullName() . ' ' . $leader->phone . ' ' . $leader->email . '<p>';
		$body = $settings->value(\App\View\AccidentReport::MAIL);
		$email->setBody($body . $leaderLine . $detail);
		$email->setHtml();
		$email->addAttachment(PROJECT_ROOT . '/www' . $selectedFile, $selectedFile);

		if (! $test)
			{
			$body = \str_replace([':', '<', '>'], ' ', $body);
			$addresses = \explode(' ', $body);

			foreach ($addresses as $address)
				{
				if (\filter_var($address, FILTER_VALIDATE_EMAIL))
					{
					$email->addCC($address);
					}
				}
			$memberPicker = new \App\Model\MemberPicker('Rides Chair');
			$email->setFromMember($memberPicker->getMember());
			$email->addBCCMember($memberPicker->getMember());

			$addresses = ['president', 'treasurer'];

			foreach ($addresses as $address)
				{
				$system = new \App\Record\SystemEmail(['mailbox' => $address]);

				if ($system->loaded())
					{
					$email->addCC($system->email, $system->name);
					}
				}

			$email->addToMember($leader->toArray());

			foreach (\App\Table\AssistantLeader::getForRide($ride) as $assistant)
				{
				$email->addToMember($assistant->toArray());
				}
			}
		else
			{
			$member = \App\Model\Session::getSignedInMember();
			$email->addToMember($member);
			$email->setFromMember($member);
			}
		$email->send();
		}
	}
