<?php

namespace App\WWW;

class SMS extends \App\View\WWWBase implements \PHPFUI\Interfaces\NanoClass
	{
	public function __toString() : string
		{
		return '';
		}

	/**
	 * Process a received SMS message.
	 *
	 * When we recieve something, it could be a reply to an all rider text, or a reply to a text to an individual.
	 * We will grant the leader special privledges, leader texts go out to whole ride.
	 * Other texts just go to the leader, not the whole ride.
	 */
	public function receive() : void
		{
		// if no post data, pass
		if (! isset($_POST['From']) || ! isset($_POST['Body']))
			{
			return;
			}

		$smsModel = new \App\Model\SMS($_POST['Body']);

		if (! $smsModel->enabled())
			{
			return;
			}

		$fromPhone = \str_replace('+1', '', (string)$_POST['From']);
		$memberPhone = $smsModel->formatPhone($fromPhone);
		$member = new \App\Record\Member();
		$member->read(['cellPhone' => $memberPhone]);

		// if not a member, ignore!
		if ($member->empty())
			{
			return;
			}

		$rideSignupTable = new \App\Table\RideSignup();
		$signups = $rideSignupTable->getMemberRidesForDate($member, \App\Tools\Date::todayString());

		if (! \count($signups))
			{
			$smsModel->setBody('We did not find a ride you signed up for today, so we can\'t forward your SMS to any ride. Sorry about that.');
			$member['allowTexting'] = 1;
			$smsModel->textMember($member);

			return;
			}

		// if member is a ride leader today, then we send out the text to the ride.
		$smsModel->setFromMember($member);

		$rideTable = new \App\Table\Ride();
		$today = \App\Tools\Date::today();
		$rides = $rideTable->getDateRange($today, $today);

		foreach ($rides as $ride)
			{
			if ($ride['memberId'] == $member->memberId)
				{
				$smsModel->textRide($ride);

				return;
				}
			}

		// if member is not a ride leader, then we send the text to the ride leader only.
		foreach ($signups as $signup)
			{
			$ride = new \App\Record\Ride($signup['rideId']);
			$leader = $ride->member;
			$smsModel->textMember($leader);

			return;
			}

		}
	}
