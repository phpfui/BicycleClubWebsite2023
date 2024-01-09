<?php

namespace App\Model;

class Volunteer
	{
	private readonly int $advanceHours;

	/** @var array<int,int> */
	private array $memberPoints = [];

	private readonly \App\Model\SettingsSaver $settingsSaver;

	public function __construct()
		{
		$this->settingsSaver = new \App\Model\SettingsSaver('VolunteerPoints');
		$this->advanceHours = (int)$this->settingsSaver->value('AdvancePostVolunteer');
		}

	public function assignRidePoints(int $daysOut = 90) : static
		{
		$categoryTable = new \App\Table\Category();
		$categoryPoints = [];
		$prefix = $this->settingsSaver->getJSONName();
		$categoryPoints[0] = (int)$this->settingsSaver->getValue($prefix . 'LeadAll');

		foreach ($categoryTable->getAllCategories() as $cat)
			{
			$categoryPoints[$cat->categoryId] = (int)$this->settingsSaver->getValue($prefix . 'Lead' . $cat->category);
			}
		$assistPoints = (int)$this->settingsSaver->getValue($prefix . 'Assist');
		$statusPoints = (int)$this->settingsSaver->getValue($prefix . 'Status');

		$yesterday = \App\Tools\Date::today() - 1;
		$rideTable = new \App\Table\Ride();
		$rides = $rideTable->getRideStatusUnawarded(\App\Tools\Date::toString($yesterday - $daysOut), \App\Tools\Date::toString($yesterday));

		foreach ($rides as $ride)
			{
			$assistantLeaders = \App\Table\AssistantLeader::getForRide($ride);

			if ($this->validateRide($ride, $assistantLeaders))
				{
				continue;
				}

			if ($ride->rideStatus > 0 && \App\Table\Ride::STATUS_NO_LEADER != $ride->rideStatus && ! $ride->pointsAwarded)
				{
				$points = $statusPoints + $categoryPoints[$ride->pace->categoryId];
				$this->addPoints($ride, $points);
				$ride->pointsAwarded = $points;
				$ride->update();
				$this->addAssistantLeaderPoints($assistantLeaders, $assistPoints);
				}
			elseif (! $ride->rideStatus && $ride->pointsAwarded)
				{
				$this->addPoints($ride, 0 - $ride->pointsAwarded);
				$ride->pointsAwarded = 0;
				$ride->update();
				$this->addAssistantLeaderPoints($assistantLeaders, 0 - $assistPoints);
				}
			}

		return $this;
		}

	public function assignRWGPSPoints(int $daysOut = 90) : static
		{
		$prefix = $this->settingsSaver->getJSONName();
		$points = (int)$this->settingsSaver->getValue($prefix . 'RWGPS');

		if ($points)
			{
			}

		return $this;
		}

	public function assignVolunteerPoints(int $daysOut = 90) : static
		{
		$prefix = $this->settingsSaver->getJSONName();
		$points = (int)$this->settingsSaver->getValue($prefix . 'Volunteer');

		if ($points)
			{
			$volunteerJobShiftTable = new \App\Table\VolunteerJobShift();
			$shifts = $volunteerJobShiftTable->getJobVolunteersSince(\App\Tools\Date::todayString(-$daysOut));

			foreach ($shifts as $shift)
				{
				$volunteerPoints = new \App\Record\VolunteerPoint();

				if (! $volunteerPoints->read($shift->toArray()))
					{
					$volunteerPoints->setFrom($shift->toArray());
					$volunteerPoints->insert();
					}

				$shift->worked = 0;

				$jobs = $volunteerJobShiftTable->getJobsForEventDateMember($shift['jobEventId'], $shift['date'], $shift['memberId']);

				foreach ($jobs as $job)
					{
					if ($job['worked'])
						{
						$shift->worked = 1;
						}
					}

				if ($shift->worked && ! $volunteerPoints->pointsAwarded)
					{
					$this->addPoints($volunteerPoints, $points);
					$volunteerPoints->pointsAwarded = $points;
					$volunteerPoints->update();
					}
				elseif (! $shift->worked && $volunteerPoints->pointsAwarded)
					{
					$this->addPoints($volunteerPoints, 0 - $volunteerPoints->pointsAwarded);
					$volunteerPoints->pointsAwarded = 0;
					$volunteerPoints->update();
					}
				}
			}

		return $this;
		}

	/**
	 * @param array<array<string,string>> $members
	 * @param array<string,array<string,string>> $files
	 */
	public function email(int $eventId, int $jobId, iterable $members, string $subject, string $message, bool $shiftInfo, array $files) : void
		{
		$email = new \App\Tools\EMail();
		$email->setSubject($subject);
		$member = \App\Model\Session::getSignedInMember();
		$name = $member['firstName'] . ' ' . $member['lastName'];
		$emailAddress = $member['email'];
		$phone = $member['phone'] ?? '';
		$email->setFromMember($member);
		$settingTable = new \App\Table\Setting();
		$server = $settingTable->value('homePage');
		$view = new \App\View\Volunteer\JobShifts(new \App\View\Page(new \App\Model\Controller(new \App\Model\Permission())));

		if (empty($files['attachment']['error']) && ! empty($files['attachment']['tmp_name']))
			{
			$email->addAttachment(\file_get_contents($files['attachment']['tmp_name']), $files['attachment']['name']);
			}

		foreach ($members as $member)
			{
			$email->setTo($member['email'], $member['firstName'] . ' ' . $member['lastName']);

			if ($jobId)
				{
				$shiftString = '<p>This email pertains to the following shift:<p>';
				}
			else
				{
				$shiftString = '<p>You have signed up for the following shifts:<p>';
				}
			$volunteerJobShiftTable = new \App\Table\VolunteerJobShift();
			$jobs = $volunteerJobShiftTable->getJobsForMember((int)$member['memberId'], $eventId);
			$hr = '';

			foreach ($jobs as $job)
				{
				if (! $jobId || $jobId == $job['jobId'])
					{
					$shiftString .= $hr;
					$hr = '<hr>';
					$shiftString .= $view->showJobShiftsFor(new \App\Record\Job($job['jobId']), new \App\Record\Member($member['memberId']), false);
					}
				}
			$shiftString .= "<p><a href='{$server}/Volunteer/myJobs'>See all your shift details here</a>";

			if (! $shiftInfo)
				{
				$shiftString = '';
				}
			$email->setBody("{$message}<p>{$shiftString}<p>This email was sent to volunteers from {$server} by<br>{$name}<br>{$emailAddress}<br>{$phone}");
			$email->setHtml();
			$email->bulkSend();
			}
		}

	public function saveMemberPoints() : static
		{
		$memberTable = new \App\Table\Member();

		foreach ($this->memberPoints as $memberId => $points)
			{
			if ($points)
				{
				$pointHistory = new \App\Record\PointHistory();
				$pointHistory->memberId = $memberId;
				$pointHistory->volunteerPoints = $points;
				$pointHistory->insert();
				$memberTable->updatePointDifference($memberId, $points);
				}
			}
		$this->memberPoints = [];

		return $this;
		}

	public function toggleEvent(\App\Record\VolunteerJobShift $vjs) : static
		{
		if (! $vjs->empty())
			{
			$vjs->worked = (int)! $vjs->worked;
			$vjs->update();
			}

		return $this;
		}

	/**
	 * @param \PHPFUI\ORM\RecordCursor<\App\Record\Member> $assistantLeaders
	 */
	public function validateRide(\App\Record\Ride $ride, \PHPFUI\ORM\RecordCursor $assistantLeaders) : string
		{
		// rides must be posted $advanceHours hours in advance to be created
		$rideTime = \strtotime($ride->rideDate . ' ' . $ride->startTime);
		$diff = $rideTime - \strtotime($ride->dateAdded);

		if ($diff < $this->advanceHours * 60 * 60)
			{
			return "The ride was not added {$this->advanceHours} before the posted start time";
			}
		$rideSignupTable = new \App\Table\RideSignup();
		$confirmedSignups = $rideSignupTable->getRidersForAttended($ride);

		if ($ride->unaffiliated)
			{
			return 'Unaffiliated ride.';
			}

		if (\count($confirmedSignups) <= \count($assistantLeaders) + 1)
			{
			$link = new \PHPFUI\Link('/Rides/confirm/' . $ride->rideId, 'Confirm here', false);

			return 'No riders (non-leaders) were confirmed for the ride. ' . $link;
			}

		return '';
		}

	private function addAssistantLeaderPoints(\PHPFUI\ORM\RecordCursor $assistantLeaders, int $assistPoints) : void
		{
		foreach ($assistantLeaders as $leader)
			{
			$this->addPoints($leader, $assistPoints);
			}
		}

	private function addPoints(\PHPFUI\ORM\Record $member, int $points) : static
		{
		if (! empty($member->memberId))
			{
			if (! isset($this->memberPoints[$member->memberId]))
				{
				$this->memberPoints[$member->memberId] = 0;
				}
			$this->memberPoints[$member->memberId] += $points;
			}

		return $this;
		}
	}
