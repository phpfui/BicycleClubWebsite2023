<?php

namespace App\Model;

class Ride
	{
	private readonly string $clubAbbrev;

	private readonly string $clubName;

	private readonly string $homePage;

	private readonly \App\Table\Pace $paceTable;

	/** @var array<string> */
	private array $protectedFields = ['pointsAwarded', 'releasePrinted', ];

	private readonly RideImages $rideImages;

	private readonly \App\Table\RideSignup $rideSignupTable;

	private readonly \App\Table\Ride $rideTable;

	private readonly \App\Table\Setting $settingTable;

	public function __construct()
		{
		$this->rideSignupTable = new \App\Table\RideSignup();
		$this->rideTable = new \App\Table\Ride();
		$this->settingTable = new \App\Table\Setting();
		$this->paceTable = new \App\Table\Pace();
		$this->rideImages = new \App\Model\RideImages();
		$this->homePage = \rtrim($this->settingTable->value('homePage'), '/');
		$this->clubName = $this->settingTable->value('clubName');
		$this->clubAbbrev = $this->settingTable->value('clubAbbrev');
	}

	/**
	 * @param array<string,mixed> $parameters
	 */
	public function add(array $parameters) : int
		{
		$parameters = $this->cleanProtectedFields($parameters);
		$parameters = $this->cleanDescription($parameters);
		$tomorrow = \App\Tools\Date::todayString(1);

		if (($parameters['rideDate'] ?? '1000-01-01') < $tomorrow)
			{
			$parameters['rideDate'] = $tomorrow;
			}

		$errors = $this->checkForStartTimeConflicts($parameters);

		if ($errors)
			{
			\App\Model\Session::setFlash('alert', $errors);
			}

		if (empty($parameters['memberId']))
			{
			$parameters['memberId'] = \App\Model\Session::signedInMemberId();
			}
		$ride = new \App\Record\Ride();
		$ride->setFrom($parameters);
		$id = $ride->insert();
		$this->rideSignupTable->deleteOtherSignedUpRides($ride, $ride->member);
		$this->addLeaderSignups($ride);

		return $id;
		}

	public function addLeaderSignups(\App\Record\Ride $ride) : void
		{
		// add leader for sure
		$this->addSignup($ride, $ride->member, \App\Table\RideSignup::DEFINITELY_RIDING);
		$assistants = \App\Table\AssistantLeader::getForRide($ride);

		foreach ($assistants as $assistant)
			{
			$this->addSignup($ride, $assistant, \App\Table\RideSignup::DEFINITELY_RIDING);
			}
		}

	public function addSignup(\App\Record\Ride $ride, \App\Record\Member $member, int $status) : void
		{
		if ($member->memberId && $ride->rideId)
			{
			$data = ['rideId' => $ride->rideId, 'memberId' => $member->memberId];
			$rideSignup = new \App\Record\RideSignup($data);
			$rideSignup->ride = $ride;
			$rideSignup->member = $member;
			$rideSignup->status = $status;

			$rideSignup->insertOrUpdate();
			}
		}

	public function cancel(\App\Record\Ride $ride, string $leaderMessage) : void
		{
		$leader = $ride->member;
		$email = new \App\Tools\EMail();
		$topic = $this->clubAbbrev . " The ride {$ride->title} on " . \App\Tools\Date::formatString('l, F j', $ride->rideDate) . ' is now leaderless';
		$email->setSubject($topic);
		$email->setFromMember($leader->toArray());
		$email->setHtml();
		$phoneLink = $leader->phone ? 'Phone: ' . \PHPFUI\Link::phone($leader->phone) : '';
		$cellLink = $leader->cellPhone ? 'Cell: ' . \PHPFUI\Link::phone($leader->cellPhone) : '';
		$emailLink = $leader->email ? \PHPFUI\Link::email($leader->email, $leader->email, $topic) : '';
		$location = $ride->startLocation;
		$locationLink = ! $location->empty() ? new \PHPFUI\Link($location->link, \App\Tools\TextHelper::unhtmlentities($location->name)) : '';
		$message = "You are receiving this email since you signed up for the following ride:<p><b>{$ride->title}</b><br>" .
			\App\Tools\Date::formatString('l, F j', $ride->rideDate) . ' starting at ' . \App\Tools\TimeHelper::toSmallTime($ride->startTime) . '<br>' .
			"{$ride->mileage} miles at a " . $this->getPace($ride->paceId) .
			" pace.<br>Starting from {$locationLink}<p>{$leaderMessage}<p>Your leader {$leader->fullName()}<br>" .
			"{$phoneLink} {$cellLink}<br>{$emailLink}<br>has decided not to lead the ride.  " .
			'Reply to this email to contact the former leader.';
		$footer = '<p>There may be other riders interested in doing this ride.<br>You can email all the other signed up riders on this ride by ' .
			"<a href='{$this->homePage}/Rides/emailRide/{$ride->rideId}'>clicking here.</a><br>";
		$footer = $footer . "<p>You can change your <a href='{$this->homePage}/Rides/signedUp/{$ride->rideId}'>signup information here.</a>";
		$message .= $footer;
		\str_replace('//', '/', $message);
		$email->setBody($message);
		$riders = $this->rideSignupTable->getAllSignedUpRiders($ride);

		foreach ($riders as $rider)
			{
			$email->addToMember($rider->toArray());
			}
		$email->bulkSend();
		$this->deleteSignup($ride, $leader);

		foreach (\App\Table\AssistantLeader::getForRide($ride) as $assistant)
			{
			$this->deleteSignup($ride, $assistant);
			}
		$ride->memberId = 0;
		$ride->rideStatus = \App\Table\Ride::STATUS_NO_LEADER;
		$ride->update();
		}

	/**
	 * @param array<string,mixed> $parameters
	 */
	public function checkForStartTimeConflicts(array $parameters) : string
		{
		$ride = new \App\Record\Ride();
		$ride->setFrom($parameters);
		$errors = new \PHPFUI\UnorderedList();
		$minutesApart = (int)$this->settingTable->value('RideMinutesApart');

		if (! $minutesApart || empty($ride->startLocationId) || empty($ride->startTime) || empty($ride->rideDate))
			{
			return \count($errors) ? "{$errors}" : '';
			}
		$startTimes = [];

		$rides = $this->rideTable->getRidesForLocation($ride->startLocationId, $ride->rideDate);

		foreach ($rides as $rideFound)
			{
			if ($rideFound->rideId != $ride->rideId)
				{
				$startTimes[] = \App\Tools\TimeHelper::fromString($rideFound->startTime);
				}
			}

		$time = \App\Tools\TimeHelper::fromString($ride->startTime);
		\sort($startTimes);

		foreach ($startTimes as $startTime)
			{
			if ($time < $startTime + $minutesApart && $time > $startTime - $minutesApart)
				{
				$errors->addItem(new \PHPFUI\ListItem('Your start time of ' . \App\Tools\TimeHelper::toString($time) . ' conflicts with a ride at ' . \App\Tools\TimeHelper::toSmallTime($ride->startTime) . '.'));
				}
			}

		if (\count($errors))
			{
			$ride->startTime = \App\Tools\TimeHelper::toMilitary($startTimes[\count($startTimes) - 1] + $minutesApart);
			$errors->addItem(new \PHPFUI\ListItem('Start time reset to ' . \App\Tools\TimeHelper::toSmallTime($ride->startTime)));
			}

		return \count($errors) ? "{$errors}" : '';
		}

	/**
	 * @param array<string,mixed> $parameters
	 *
	 * @return array<string,mixed>
	 */
	public function cleanDescription(array $parameters) : array
		{
		$RWGPSId = $this->stripRideWithGPS($parameters['description']);

		if (! $RWGPSId)
			{
			$RWGPSId = \App\Model\RideWithGPS::getRWGPSIdFromLink($parameters['RWGPSurl'] ?? '');
			}

		if (! $RWGPSId)
			{
			$RWGPSId = (int)($parameters['RWGPSId'] ?? 0);
			}

		if ($RWGPSId)
			{
			$rwgps = new \App\Record\RWGPS($RWGPSId);

			if (! $rwgps->loaded())
				{
				$rwgps->RWGPSId = $RWGPSId;
				$rwgps->insertOrUpdate();
				}
			}

		if ($RWGPSId)
			{
			$parameters['RWGPSId'] = $RWGPSId;
			}
		$parameters['description'] = \App\Tools\TextHelper::cleanUserHtml($parameters['description']);

		return $parameters;
		}

	/**
	 * Compute a ride's duration.  Assume 1/2 hour break.
	 *
	 * @return int seconds
	 */
	public function computeDuration(\App\Record\Ride $ride) : int
		{
		$seconds = 4 * 60 * 60;
		$mileage = (int)$ride->mileage;

		if (! $mileage)
			{
			return $seconds;
			}
		$count = 0;
		$rides = $this->rideTable->pastRidesForMember($ride->member, 0);
		$average = 0.0;

		foreach ($rides as $pastRide)
			{
			if ($pastRide['paceId'] == $ride->paceId)
				{
				$averagePace = $pastRide['averagePace'] ?? 0.0;

				if ($averagePace < 25.0 && $averagePace > 5.0)
					{
					$average += $averagePace;
					++$count;
					}
				}

			if ($count >= 10)
				{
				break;
				}
			}

		if (! $average || ! $count)
			{
			return $seconds;
			}
		$average /= $count;
		$seconds = (int)((float)$mileage / $average * 3600 + 30 * 60);

		return $seconds;
		}

	public function delete(int $rideId) : void
		{
		$ride = new \App\Record\Ride($rideId);
		$ride->delete();
		}

	public function deleteSignup(\App\Record\Ride $ride, \App\Record\Member $member) : void
		{
		if ($member->loaded())
			{
			$condition = new \PHPFUI\ORM\Condition('rideId', $ride->rideId);
			$condition->and('memberId', $member->memberId);
			$this->rideSignupTable->setWhere($condition);
			$this->rideSignupTable->delete();
			}
		}

	public function downloadCSV(\PHPFUI\ORM\DataObjectCursor $rides) : void
		{
		$writer = new \App\Tools\CSV\FileWriter('rides.csv');
		$writer->addHeaderRow();
		$relations = [
			'cueSheetId' => 'name',
			'paceId' => 'pace',
			'startLocationId' => 'name',
		];
		$status = \App\Table\Ride::getStatusValues();

		foreach ($rides as $ride)
			{
			$ride->rideId = null;
			$ride->description = null;
			$ride->rideStatus = $status[$ride->rideStatus] ?? 'not reported';
			$row = $ride->toArray();

			foreach ($relations as $relationId => $field)
				{
				$relation = \substr($relationId, 0, \strlen($relationId) - 2);
				$row[$relation] = $ride->{$relationId}->{$field} ?? '';
				}
			$writer->outputRow($row);
			}
		}

	/**
	 * Send out ride notices
	 *
	 * @param \App\Record\Ride $ride to send out
	 */
	public function emailNewRideNotice(\App\Record\Ride $ride) : void
		{
		$daysInAdvance = \App\Tools\Date::fromString($ride->rideDate) - \App\Tools\Date::today();

		$week = 'this ';

		if ($daysInAdvance >= 7)
			{
			$week = 'next ';
			}
		$leader = $ride->member;
		$title = $this->clubAbbrev . ' Reminder: ' . $this->getPace($ride->paceId) . ' ride ' . $week .
			\App\Tools\Date::formatString('l', $ride->rideDate);

		$message = $this->getRideNoticeBody($ride, $leader);

		$message .= '<p>Reply to this email to ask the leader any specific questions.' .
			"<br><a href='{$this->homePage}/Membership/myNotifications'>Change your notification settings</a>." .
			'<br>Enter 0 under Ride Reminder Settings to turn this feature off.</p>';
		$email = new \App\Tools\EMail();
		$email->setSubject($title);
		$email->setFromMember($leader->toArray());
		$email->setBody($message);
		$email->setHtml();
		$calendar = $this->getCalendarObject($ride, $leader);

		if ($calendar)
			{
			$fileName = $this->clubAbbrev . "Ride{$ride->rideId}.ics";
			$email->addAttachment($calendar->export(), $fileName);
			}
		$categoryId = $this->paceTable->getCategoryIdFromPaceId($ride->paceId);

		$memberTable = new \App\Table\Member();
		$members = $memberTable->getNewRideInterests($categoryId);

		if (\count($members))
			{
			foreach ($members as $member)
				{
				$email->addToMember($member);
				}
			$email->bulkSend();
			}
		}

	public function getCalendarObject(\App\Record\Ride $ride, \App\Record\Member $leader) : ?\ICalendarOrg\ZCiCal
		{
		// create the ical object
		$icalobj = new \ICalendarOrg\ZCiCal();

		// create the event within the ical object
		$eventobj = new \ICalendarOrg\ZCiCalNode('VEVENT', $icalobj->curnode);

		// add title
		$title = $this->getPace($ride->paceId) . ' ' . $ride->mileage . ' ' . $ride->title;
		$eventobj->addNode(new \ICalendarOrg\ZCiCalDataNode('SUMMARY:' . $title));
		$eventobj->addNode(new \ICalendarOrg\ZCiCalDataNode('METHOD:REQUEST'));

		// add start date
		$startTime = \App\Tools\Date::getUnixTimeStamp($ride->rideDate, $ride->startTime);

		if (empty($startTime))
			{
			return null;
			}
		$eventobj->addNode(new \ICalendarOrg\ZCiCalDataNode('DTSTART:TZID=' . \date_default_timezone_get() . ':' . \gmdate('Ymd\THis', $startTime)));

		// add end date
		$endTime = $startTime + $this->computeDuration($ride);
		$eventobj->addNode(new \ICalendarOrg\ZCiCalDataNode('DTEND::TZID=' . \date_default_timezone_get() . ':' . \gmdate('Ymd\THis', $endTime)));

		// UID is a required item in VEVENT, create unique string for this event
		// Adding your domain to the end is a good way of creating uniqueness

		$uid = "{$this->homePage}/Rides/signedUp/{$ride->rideId}";
		$eventobj->addNode(new \ICalendarOrg\ZCiCalDataNode('UID:' . $uid));

		// DTSTAMP is a required item in VEVENT
		$eventobj->addNode(new \ICalendarOrg\ZCiCalDataNode('DTSTAMP:' . \ICalendarOrg\ZDateHelper::fromSqlDateTime()));

		if ($leader->loaded())
			{
			$eventobj->addNode(new \ICalendarOrg\ZCiCalDataNode("ORGANIZER;CN={$leader->fullName()}:mailto:{$leader->email}"));
			}

		$startLocation = $ride->startLocation;

		if ($startLocation->name)
			{
			$location = 'LOCATION';
			$link = $startLocation->link ?? '';

			$location .= ';ALTREP="' . $link . '"';

			$location .= ":{$startLocation->name}";
			$eventobj->addNode(new \ICalendarOrg\ZCiCalDataNode($location));
			}

		// Add description
		$this->processDescription($ride->description) . "\nSign Up: {$uid}";
		$description = $this->processDescription($ride->description);
		$eventobj->addNode(new \ICalendarOrg\ZCiCalDataNode('DESCRIPTION:' . \ICalendarOrg\ZCiCal::formatContent($description)));

		return $icalobj;
		}

	public function getRideNoticeBody(\App\Record\Ride $ride, \App\Record\Member $leader = new \App\Record\Member()) : string
		{
		$signedUpRiders = $this->rideSignupTable->getSignedUpRiders($ride->rideId);
		$signup = '';

		if (\count($signedUpRiders))
			{
			$signup = '<p>The following riders have already signed up as of ' . \date('g:i A') . ' (in signup order):</p>';
			$status = $this->rideSignupTable->getRiderStatus();
			unset($status[\App\Table\RideSignup::POSSIBLY_RIDING]);

			$ul = new \PHPFUI\UnorderedList();

			foreach ($signedUpRiders as $rider)
				{
				if (! $rider->showNoRideSignup)
					{
					$ul->addItem(new \PHPFUI\ListItem("{$rider->firstName} {$rider->lastName} {$status[$rider->status]}"));
					}
				}
			$signup .= $ul;
			}
		$location = $ride->startLocation;
		$locationText = '';

		if (! $location->empty())
			{
			$locationText = 'Starting from ' . \App\View\StartLocation::getTextFromArray($location->toArray()) . ',<br>';
			}

		$message = '<a href="' . $this->homePage . '">' . $this->clubName . '</a> would like to remind you of an upcoming ride:<br><br>'
			. 'On <b>' . \App\Tools\Date::formatString('l, F j', $ride->rideDate) . '</b> starting at <b>' . \App\Tools\TimeHelper::toSmallTime($ride->startTime) . '</b><br>'
			. "{$ride->mileage} miles at a " . $this->getPace($ride->paceId) . ' pace.';
			$message .= ' Targeted average: ' . \number_format($ride->targetPace, 1);
			$message .= "<br>{$locationText}";

		if ($leader->empty())
			{
			$leader = $ride->member;
			}

		if ($leader->empty())
			{
			$message .= 'No Leader';

			if (\App\Table\Ride::STATUS_NO_LEADER == $ride->rideStatus)
				{
				$message .= ': Ride Cancelled';
				}
			$this->getRidesChair();
			}
		else
			{
			$message .= "Led by <b>{$leader->fullName()}</b> " . \PHPFUI\Link::email($leader->email);

			if (\strlen($leader->phone) >= 7)
				{
				$message .= ' Phone: ' . \PHPFUI\Link::phone($leader->phone);
				}

			if (\strlen($leader->cellPhone) >= 7)
				{
				$message .= ' Cell: ' . \PHPFUI\Link::phone($leader->cellPhone);
				}
			}
		$message .= '<br><br>';
		$button = new \PHPFUI\EMailButton('Sign Up For This Ride', $this->homePage . '/Rides/signedUp/' . $ride->rideId);
		$title = \App\Tools\TextHelper::unhtmlentities($ride->title);
		$description = $this->processDescription($ride->description);
		$rwgpsLink = '';

		if ($ride->RWGPSId)
			{
			$link = new \PHPFUI\Link($ride->RWGPS->routeLink());
			$rwgpsLink = "<p>{$link}</p>";
			}
		$message .= "<b>{$title}</b><p>{$description}</p>" . $rwgpsLink . '<p>Sign up for this ride now!</p><br>' . $button . $signup;

		return $message;
		}

	/**
	 * @return array<string,mixed>
	 */
	public function getRidesChair() : array
		{
		$memberPicker = new \App\Model\MemberPicker('Rides Chair');

		return $memberPicker->getMember();
		}

	public function isLeaderOrAssistant(\App\Record\Ride $ride, int $memberId = 0) : bool
		{
		if (! $memberId)
			{
			$memberId = \App\Model\Session::signedInMemberId();
			}

		if ($ride->memberId == $memberId)
			{
			return true;
			}
		$assistants = \App\Table\AssistantLeader::getForRide($ride);

		foreach ($assistants as $assistant)
			{
			if ($assistant->memberId == $memberId)
				{
				return true;
				}
			}

		return false;
		}

	/**
	 * @param array<string,string> $parameters
	 *
	 * @return string errors found during save, empty if no errors
	 */
	public function save(array $parameters) : string
		{
		$parameters = $this->cleanProtectedFields($parameters);
		$parameters = $this->cleanDescription($parameters);

		$differences = $this->getDifferences($parameters);
		$warningDays = (int)$this->settingTable->value('RideEditedWarningDays');

		if (\count($differences) && $warningDays)
			{
			$oldRide = new \App\Record\Ride($parameters['rideId']);
			$oldRideTime = \strtotime($oldRide->rideDate . ' ' . ($oldRide->startTime ?? '9:00 AM'));

			if (\App\Tools\Date::fromString($oldRide->rideDate) - $warningDays <= \App\Tools\Date::today() && $oldRideTime >= \time())
				{
				$email = new \App\Tools\EMail();
				$abbrev = $this->clubAbbrev;
				$email->setSubject("A {$abbrev} ride you have signed up for has changed");
				$member = \App\Model\Session::getSignedInMember();
				$email->setFromMember($member);
				$body = "<b>{$member['firstName']} {$member['lastName']}</b> updated the ride titled <b>{$oldRide->title}</b> scheduled for ";
				$body .= $oldRide->rideDate;
				$body .= '<p>And changed:</p>';

				foreach ($differences as $name => $diffArray)
					{
					if ('description' == $name)
						{
						$body .= "<p>The <b>{$name}</b> to: " . $this->processDescription($diffArray['to']) . '</p>';
						}
					else
						{
						$body .= "<p>The <b>{$name}</b> from: <i>{$diffArray['from']}</i> to: <b>{$diffArray['to']}</b>.</p>";
						}
					}
				$email->setBody(\App\Tools\TextHelper::cleanUserHtml($body));
				$email->setHtml();
				$members = $this->rideSignupTable->getSignedUpRiders($parameters['rideId']);

				foreach ($members as $member)
					{
					$email->addBccMember($member->toArray());
					}
				$email->bulkSend();
				}
			}

		// if the ride status is not yet, but they have an average pace and riders
		if (empty($parameters['rideStatus']) && ! empty($parameters['averagePace']) && ! empty($parameters['numberOfRiders']))
			{
			$parameters['rideStatus'] = \App\Table\Ride::STATUS_COMPLETED;  // then set the status to completed
			}

		if (! empty($parameters['memberId']))
			{
			$ride = new \App\Record\Ride($parameters['rideId']);

			if (\App\Table\Ride::STATUS_NO_LEADER == $ride->rideStatus) // was cancelled, but now has leader
				{
				$parameters['rideStatus'] = \App\Table\Ride::STATUS_NOT_YET;
				}
			}
		else
			{
			$parameters['rideStatus'] = \App\Table\Ride::STATUS_NO_LEADER;
			}
		$error = $this->checkForStartTimeConflicts($parameters);

		$ride = new \App\Record\Ride($parameters['rideId']);
		$ride->setFrom($parameters);
		$ride->update();

		// leader can't be on another ride
		$this->rideSignupTable->deleteOtherSignedUpRides($ride, $ride->member);
		$this->addLeaderSignups($ride);

		return $error;
		}

	/**
	 * @param array<string,string> $parameters
	 *
	 * @return array<string,string>
	 */
	private function cleanProtectedFields(array $parameters) : array
		{
		foreach ($this->protectedFields as $field)
			{
			unset($parameters[$field]);
			}

		return $parameters;
		}

	private function getCueSheet(int $cueSheetId) : string
		{
		$cuesheet = new \App\Record\CueSheet($cueSheetId);

		if ($cuesheet->loaded())
			{
			return $cuesheet->getFullNameLink();
			}

		return 'No cue sheet';
		}

	/**
	 * @param array<string,mixed> $parameters
	 *
	 * @return array<string,array<string,string>>
	 */
	private function getDifferences(array $parameters) : array
		{
		$diffs = [];
		$ride = new \App\Record\Ride($parameters['rideId']);

		foreach($ride->toArray() as $field => $value)
			{
			// adjust fields due to browser issues
			if ('startTime' == $field)
				{
				$value = \App\Tools\TimeHelper::roundToInterval($value ?? '');

				if (isset($parameters['startTime']))
					{
					$parameters['startTime'] = \App\Tools\TimeHelper::roundToInterval(\App\Tools\TimeHelper::toString(\App\Tools\TimeHelper::fromString($parameters['startTime'])));
					}
				}

			if(isset($parameters[$field]) && $value != $parameters[$field])
				{
				$from = $to = $name = '';

				switch($field)
					{
					case 'startTime':
						$name = 'start time';

						// Intentionally fall through
					case 'mileage':

						if(empty($name))
							{
							$name = $field;
							}
						$from = $value;
						$to = $parameters[$field];

						break;


					case 'rideDate':

						$name = 'date';
						$from = $value;
						$to = $parameters[$field];

						break;


					case 'cueSheetId':

						$to = $this->getCueSheet($parameters[$field]);
						$from = $this->getCueSheet((int)$value);
						$name = 'cue sheet';

						break;


					case 'paceId':

						$to = $this->getPace($parameters[$field]);
						$from = $this->getPace($value);
						$name = 'pace';

						break;


					case 'memberId':

						$to = $this->getMember((int)$parameters[$field]);
						$from = $this->getMember($value);
						$name = 'leader';

						break;


					case 'RWGPSId':

						$toRWGPS = new \App\Record\RWGPS((int)$parameters[$field]);
						$to = $toRWGPS->routeLink();
						$fromRWGPS = new \App\Record\RWGPS((int)$value);
						$from = $fromRWGPS->routeLink();
						$name = 'Ride With GPS';

						break;


					case 'startLocationId':

						$to = $this->getStartLocation((int)$parameters[$field]);
						$from = $this->getStartLocation((int)$value);
						$name = 'start location';

						break;


					case 'description':
						// do nothing with description
						break;

					default:

						$name = $field;
						$from = $value;
						$to = $parameters[$field];

						break;

					}

				if ($from)
					{
					$diffs[$name] = ['from' => $from, 'to' => $to];
					}
				}
			}

		return $diffs;
		}

	private function getMember(int $memberId) : string
		{
		$member = new \App\Record\Member($memberId);

		return $member->fullName();
		}

	private function getPace(?int $paceId) : string
		{
		$pace = new \App\Record\Pace($paceId);

		return $pace->empty() ? 'All' : $pace->pace;
		}

	private function getStartLocation(int $startLocationId) : string
		{
		$sl = new \App\Record\StartLocation($startLocationId);

		return $sl->name ?? 'no start location';
		}

	private function processDescription(?string $description) : string
		{
		$description = \App\Tools\TextHelper::unhtmlentities($description ?? '');
		$url = $this->settingTable->value('homePage');
		$description = \str_replace('/' . $this->rideImages->getFileType(), $url . '/' . $this->rideImages->getFileType(), $description);

		return $description;
		}

	/**
	 * Removes the RWGPS link and returns it as an int. Modifies passed in string
	 */
	private function stripRideWithGPS(?string &$description) : int
		{
		if (null === $description)
			{
			$description = '';

			return 0;
			}
		$RWGPSId = 0;
		$description = \str_replace('rwgps.com', 'ridewithgps.com', $description);

		$description = \App\Tools\TextHelper::addRideLinks($description, true);

		if (! \str_contains($description, 'ridewithgps'))
			{
			return $RWGPSId;
			}

		$dom = new \voku\helper\HtmlDomParser($description);

		foreach ($dom->find('a') as $node)
			{
			if (\str_contains($node->getAttribute('href'), 'ridewithgps'))
				{
				$RWGPSId = \App\Model\RideWithGPS::getRWGPSIdFromLink($node->getAttribute('href'));

				if ($RWGPSId)
					{
					// delete the found nodes
					$node->outertext = '';
					}
				}
			}

		$description = "{$dom}";

		return $RWGPSId;
		}
	}
