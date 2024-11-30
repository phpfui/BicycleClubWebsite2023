<?php

namespace App\Model;

class RideSignup
	{
	private readonly \App\Table\RideSignup $rideSignupTable;

	private readonly \App\Table\Setting $settingTable;

	private int $signupLimit = 0;

	public function __construct(private readonly \App\Record\Ride $ride, private readonly \App\Record\Member $member)
		{
		$this->rideSignupTable = new \App\Table\RideSignup();
		$this->settingTable = new \App\Table\Setting();
		$pace = new \App\Record\Pace($this->ride->paceId);

		$this->signupLimit = $signupLimit = (int)$this->settingTable->value('RideSignupLimit');

		if (-1 != $pace->maxRiders)
			{
			$this->signupLimit = $pace->maxRiders;
			}

		if (! $signupLimit && ! $this->ride->empty() && $this->ride->maxRiders > 0)
			{
			$this->signupLimit = $this->ride->maxRiders;
			}
		}

	public function copyWaitList(\App\Record\Ride $clonedRide) : void
		{
		$this->rideSignupTable->moveWaitListToRideFromRide($this->ride, $clonedRide);
		$this->notifyWaitList();
		}

	public function getRiderSignupLimit() : int
		{
		return $this->signupLimit;
		}

	public function notifyWaitList() : void
		{
		// no wait list for old rides
		if ($this->ride->rideDate < \App\Tools\Date::todayString())
			{
			return;
			}

		while (true)
			{
			$definites = $this->rideSignupTable->getRidersForStatus($this->ride, \App\Enum\RideSignup\Status::DEFINITELY_RIDING);

			if (\count($definites) >= $this->signupLimit)
				{
				return;
				}

			$waitList = $this->rideSignupTable->getRidersForStatus($this->ride, \App\Enum\RideSignup\Status::WAIT_LIST);

			if (! \count($waitList))
				{
				return;
				}

			$rider = $waitList->current();
			$rider->status = \App\Enum\RideSignup\Status::DEFINITELY_RIDING;
//			$rider->signedUpTime = \date('Y-m-d H:i:s');
			$this->rideSignupTable->deleteOtherSignedUpRides($this->ride, $rider->member);
			$rider->rideId = $this->ride->rideId;
			$rider->update();

			$email = new \App\Tools\EMail();
			$email->setSubject($this->settingTable->value('clubAbbrev') . ': You have been taken off the wait list');
			$member = $rider->member;
			$container = new \PHPFUI\Container();
			$container->add('You have been taken off the wait list and are now a confirmed rider for the following ride:');
			$ul = new \PHPFUI\UnorderedList();
			$ul->addItem(new \PHPFUI\ListItem('Date: ' . $this->ride->rideDate));
			$ul->addItem(new \PHPFUI\ListItem('Time: ' . \App\Tools\TimeHelper::toSmallTime($this->ride->startTime)));
			$url = $this->settingTable->value('homePage');

			$ul->addItem(new \PHPFUI\ListItem(new \PHPFUI\Link($url . "/Rides/signedup/{$this->ride->rideId}", $this->ride->title ?? '')));
			$container->add($ul);
			$container->add('If you can\'t make it, please remove yourself from the ride at the link above.<br><br>Thank you.');
			$email->setHtml();
			$email->setBody($container);
			$email->setToMember($member->toArray());
			$leader = $this->ride->member;
			$email->setFromMember($leader->toArray());

			if ($this->ride->signupNotifications)
				{
				$email->addBccMember($leader->toArray());
				}
			$email->setHtml();
			$email->send();
			}
		}

	/**
	 * @param array<string,mixed> $fields
	 */
	public function updateSignup(array $fields) : void
		{
		// make sure any wait list people are processed first
		$this->notifyWaitList();
		$action = '';
		$key = ['rideId' => $this->ride->rideId, 'memberId' => $this->member->memberId, ];
		$rider = new \App\Record\RideSignup($key);
		$status = $this->rideSignupTable->getRiderStatus();
		$definites = $this->rideSignupTable->getRidersForStatus($this->ride, \App\Enum\RideSignup\Status::DEFINITELY_RIDING);
		$waitlisted = $this->rideSignupTable->getRidersForStatus($this->ride, \App\Enum\RideSignup\Status::WAIT_LIST);

		if (empty($fields['status']))
			{
			$rideTime = \strtotime($this->ride->rideDate . ' ' . \App\Tools\TimeHelper::toSmallTime($this->ride->startTime));

			// if there is a waitlist, and rider is definately riding, and ride within 24 hours of ride start, and signed up 24 hours before ride, then mark as cancelled
			if (\count($waitlisted) &&
					\App\Enum\RideSignup\Status::DEFINITELY_RIDING == ($rider->status ?? 0) &&
					$rideTime - \time() < 24 * 60 * 60 &&
					$rider->signedUpTime < \date('Y-m-d H:i:s', $rideTime - 86400))
				{
				$rider->status = \App\Enum\RideSignup\Status::CANCELLED;
				$rider->update();
				}
			else
				{
				$rider->delete();
				}

			if ($rider->loaded())
				{
				$action = 'removed themselves as a registered rider';
				}
			}
		else
			{
			if ($rider->loaded())
				{
				if ($fields['status'] != $rider->status)
					{
					if (\App\Enum\RideSignup\Status::DEFINITELY_RIDING == $fields['status'] && $this->signupLimit && \count($definites) >= $this->signupLimit)
						{
						$fields['status'] = \App\Enum\RideSignup\Status::WAIT_LIST;
						\App\Model\Session::setFlash('alert', 'Ride is full, you are now on the wait list.');
						}
					$rider->setFrom($fields);
					$rider->update();
					$action = "changed their status to <b>{$status[$fields['status']]}</b>";
					}
				}
			else
				{
				if ($this->signupLimit && \count($definites) >= $this->signupLimit)
					{
					$fields['status'] = \App\Enum\RideSignup\Status::WAIT_LIST;
					\App\Model\Session::setFlash('alert', 'Ride is full, you are now on the wait list.');
					}
				$rider->setFrom($fields);
				$rider->signedUpTime = \date('Y-m-d H:i:s');
				$signedUpTime = $this->rideSignupTable->getEarliestRiderSignupTime($this->member, $this->ride->rideDate);

				if ($signedUpTime)
					{
					$rider->signedUpTime = $signedUpTime;
					}
				$rider->insert();
				$action = "signed up as <b>{$status[$fields['status']]}</b>";
				}

			if ($rider->comments && $fields['comments'] != $rider->comments)
				{
				$action .= ' and changed the comment to:<br><b>' . \App\Tools\TextHelper::htmlentities($fields['comments']) . '</b>';
				}
			elseif ($fields['comments'])
				{
				$action .= ' and left the comment:<br><b>' . \App\Tools\TextHelper::htmlentities($fields['comments']) . '</b>';
				}
			}
		unset($fields['rideComments']);

		if (\App\Enum\RideSignup\Status::DEFINITELY_RIDING == ($fields['status'] ?? -1) && $this->signupLimit)
			{
			$this->rideSignupTable->deleteOtherSignedUpRides($this->ride, $this->member);
			}

		// if they are updating their own info, then save it
		if (\App\Model\Session::signedInMemberId() == $this->member->memberId)
			{
			$this->member->setFrom($fields);
			$this->member->update();
			}

		$leader = $this->ride->member;

		if ($action && $this->ride->signupNotifications)
			{
			if ($leader->loaded())
				{
				$email = new \App\Tools\EMail();
				$email->setSubject('Rider signup change');
				$body = 'For your ride on ' . $this->ride->rideDate;
				$body .= ", {$this->member->fullName()} has {$action}.";

				if ($this->member->memberId != \App\Model\Session::signedInMemberId())
					{
					$changer = \App\Model\Session::signedInMemberRecord();
					$email->addToMember($changer->toArray());
					$email->addToMember($this->member->toArray());
					$body .= '<br><br>This change was made by ' . $changer->fullName();
					}
				$email->setBody($body);
				$email->addToMember($leader->toArray());
				$email->setFromMember($this->member->toArray());
				$email->setHtml();
				$email->send();
				}
			}
		$title = $this->settingTable->value('newRiderEmailTitle');

		if ($leader->loaded() && $title)
			{
			$rideSignupTable = new \App\Table\RideSignup();
			$condition = new \PHPFUI\ORM\Condition('memberId', $this->member->memberId);
			$condition->and('attended', \App\Enum\RideSignup\Attended::CONFIRMED);
			$rideSignupTable->setWhere($condition);

			if (! $rideSignupTable->count())
				{
				$email = new \App\Tools\EMail();
				$email->setSubject($title);
				$message = \App\Tools\TextHelper::processText($this->settingTable->value('newRiderEmail'), $this->member->toArray());
				$email->setBody($message);
				$email->setToMember($this->member->toArray());
				$email->setFromMember($this->ride->member->toArray());
				$email->setHtml();
				$email->send();
				}
			}

		$this->notifyWaitList();
		}
	}
