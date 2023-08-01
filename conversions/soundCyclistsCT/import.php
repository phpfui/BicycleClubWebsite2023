<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

$permissions = [
	'Ride Leader' => 2,
	'Board Member' => 18,
	'Ride Coordinator' => 3,
	'Ride Director' => 4,
];

function diff(array $one, array $two) : array
	{
	$retVal = [];

	foreach ($one as $key => $value)
		{
		if ($value != $two[$key])
			{
			$retVal[$key] = [$value, $two[$key]];
			}
		}

	return $retVal;
	}

function cleanEmail(string $email) : string
	{
	$email = \trim(\strtolower($email));

	if (\str_ends_with($email, '@gmail.com'))
		{
		$email = \str_replace('.', '', $email);
		$email = \str_replace('@gmailcom', '@gmail.com', $email);
		}

	if (! \filter_var($email, FILTER_VALIDATE_EMAIL))
		{
		$email = '';
		}

	return $email;
	}

$cleanStates = [
	'UNK' => 'CT',
	'VIRG' => 'VA',
	'SELECT' => 'CT',
	'CALI' => 'CA',
	'FLOR' => 'FL',
	'VERMONT' => 'VT',
	'YORK' => 'NY',
	'JERSEY' => 'NJ',
	'MASS' => 'MA',
	'MARYLAND' => 'MD',
	'ST' => 'CT',
	'HAMDEN' => 'CT',
	'MIDLOTHIAN' => 'CT',
	'CR' => 'CT',
	'NORWALK' => 'CT',
	'MAIN' => 'ME',
	'NEVA' => 'NV',
	'WILTON' => 'CT',
	'CY' => 'CT',
	'RHODEISLAND' => 'RI',
	'STAMFORD' => 'CT',
	'CONN' => 'CT',
];

function cleanState(string $state) : string
	{
	global $cleanStates;

	$state = \trim(\strtoupper(\str_replace([' ', '.'], '', $state)));

	foreach ($cleanStates as $full => $abbrev)
		{
		if (\str_contains($state, $full))
			{
			return $abbrev;
			}
		}

	return $state;
	}

function getMember(string $fullName) : \App\Record\Member
	{
	$names = \explode(' ', $fullName);

	$memberTable = new \App\Table\Member();

	if (\count($names) >= 2)
		{
		$condition = new \PHPFUI\ORM\Condition('firstName', $names[0]);
		$condition->and(new \PHPFUI\ORM\Condition('lastName', $names[1]));
		$memberTable->setWhere($condition);

		return $memberTable->getRecordCursor()->current();
		}
	$member = new \App\Record\Member();

	return $member;
	}

function makeDate(string $mdyFormat) : ?string
	{
	if (! \str_contains($mdyFormat, '/'))
		{
		return null;
		}

	[$month, $day, $year] = \explode('/', $mdyFormat);

	$retVal = \App\Tools\Date::makeString($year, $month, $day);

	if ('2099-12-31' < $retVal)
		{
		$retVal = '2099-12-31';
		}

	return $retVal;
	}

function getReader(string $fileName) : \App\Tools\CSVReader
	{
	$fileName = PROJECT_ROOT . '/conversions/soundCyclistsCT/zoho/tables/' . $fileName;

	if (! \file_exists($fileName))
		{
		echo "File {$fileName} was not found\n";

		exit;
		}

	return new \App\Tools\CSVReader($fileName);
	}

$dataPurger = new \App\Model\DataPurge();
$dataPurger->addExceptionTable(new \App\Table\Setting());
$dataPurger->addExceptionTable(new \App\Table\Blog());
$dataPurger->addExceptionTable(new \App\Table\Story());
$dataPurger->addExceptionTable(new \App\Table\Permission());
$dataPurger->addExceptionTable(new \App\Table\PermissionGroup());
$dataPurger->purge();

/**
 * import categories
 *
 * A,20+ mph,Very Fast,Ride Coordinator A  (Very Fast 18+ mph),Active
 *
 * 'category' => ['char(2)', 'string', 2, true, '', false, ],
 * 'categoryId' => ['int(11)', 'int', 11, false, 0, true, ],
 * 'coordinator' => ['int(11)', 'int', 11, false, 0, false, ],
 * 'description' => ['varchar(100)', 'string', 100, true, '', false, ],
 * 'maxSpeed' => ['varchar(5)', 'string', 5, true, '', false, ],
 * 'memberDefault' => ['tinyint(1)', 'int', 1, false, 0, false, ],
 * 'minSpeed' => ['varchar(5)', 'string', 5, true, '', false, ],
 * 'ordering' => ['int(11)', 'int', 11, false, 999999, false, ],
 *
 * Ride Level
 * Pace
 * Pace Description
 * Board Positions
 * Status
 */
$categoryReader = \getReader('Ride Level Master View.csv');

$ordering = 0;

$paces = [];
$categories = [];

foreach ($categoryReader as $row)
	{
	if ('Inactive' == $row['Status'])
		{
		continue;
		}
	$category = new \App\Record\Category();
	$category->category = $row['Ride Level'];
	$category->description = $row['Pace Description'];
	$category->ordering = ++$ordering;
	$pace = $row['Pace'];
	$pace = \App\Tools\TextHelper::properCase(\str_replace('mph', '', $pace));
	$parts = \explode('-', $pace);
	$category->minSpeed = '';
	$category->maxSpeed = '';

	if (\count($parts) > 1)
		{
		$category->minSpeed = $parts[0];
		$category->maxSpeed = $parts[1];
		}
	elseif ((int)$parts[0])
		{
		$category->minSpeed = $parts[0];
		$category->maxSpeed = '25';
		}
	$categories[$category->category] = $category->insert();
	$pace = new \App\Record\Pace();
	$pace->category = $category;
	$pace->pace = $category->category;
	$pace->maxSpeed = $category->maxSpeed;
	$pace->minSpeed = $category->minSpeed;
	$paces[$pace->pace] = $pace->insert();
	}

/**
 * Last Name
 * First Name
 * Member ID
 * Status
 * Membership Expires
 * Ride Levels of Interest
 * Club Roles
 * Street Address
 * City
 * State
 * Postal Code
 * Home Phone
 * Cell Phone
 * Email
 * Age Category
 * Gender
 * Contact Person
 * Contact Person Primary Phone
 * Member Since
 * Weekly Ride Schedule Email
 * Emails for Bike Shop Offers
 * Emails for 3rd Party Events
 * Member Type
 * Additional Household Members
 * primary_last_name
 * primary_first_name
 * Record ID
 * Added Time
 * Last Modified Time
 *
 * Payments
 *
 * 'amount' => ['decimal(6,2)', 'float', 6, true, 0, false, ],
 * 'dateReceived' => ['date', 'string', 10, false, '', false, ],
 * 'enteringMemberNumber' => ['int(6)', 'int', 6, true, 0, false, ],
 * 'invoiceId' => ['int(11)', 'int', 11, true, 0, false, ],
 * 'membershipId' => ['int(11)', 'int', 11, true, 0, false, ],
 * 'paymentDated' => ['date', 'string', 10, true, '', false, ],
 * 'paymentId' => ['int(11)', 'int', 11, false, 0, true, ],
 * 'paymentNumber' => ['char(20)', 'string', 20, true, '', false, ],
 * 'paymentType' => ['int(1)', 'int', 1, true, 0, false, ],
 *
 * Ride Levels of Interest
 */
$addBruce = new \App\Cron\Job\AddBruce(new \App\Cron\Controller(5));
$addBruce->run();

$dupMembers = ['csadler88@yahoo.com', 'lpelecovich@odysseyre.com'];

$memberReader = \getReader('Member Information View.csv');

$memberships = [];

foreach ($memberReader as $row)
	{
	$email = \cleanEmail($row['Member ID']);

	if (\in_array($email, $dupMembers) && 'Active' != $row['Status'])
		{
		continue;
		}

	$key = \App\Tools\TextHelper::properCase($row['Street Address']) . '|' . \trim($row['Postal Code']);

	if (! isset($memberships[$key]))
		{
		$memberships[$key] = [];
		}
	$memberships[$key][] = $row;
	}

foreach ($memberships as $members)
	{
	$membership = new \App\Record\Membership();
	$membership->joined = \makeDate($members[0]['Member Since']);
	$membership->address = \App\Tools\TextHelper::properCase($members[0]['Street Address']);
	$membership->allowedMembers = 10;
	$membership->expires = \makeDate($members[0]['Membership Expires']);
	$membership->pending = 0;
	$membership->state = \cleanState($members[0]['State']);
	$membership->town = \App\Tools\TextHelper::properCase($members[0]['City']);
	$membership->zip = \App\Tools\TextHelper::properCase($members[0]['Postal Code']);

	$uniqueMembers = [];

	foreach ($members as $row)
		{
		$member = new \App\Record\Member();
		$member->firstName = \App\Tools\TextHelper::properCase($row['First Name']);
		$member->lastName = \App\Tools\TextHelper::properCase($row['Last Name']);
		$member->email = \cleanEmail($row['Member ID']);
		$member->cellPhone = \App\Tools\TextHelper::formatPhone($row['Cell Phone']);
		$member->phone = \App\Tools\TextHelper::formatPhone($row['Home Phone']);
		$member->membership = $membership;
		$member->emergencyContact = \App\Tools\TextHelper::properCase($row['Contact Person']);
		$member->emergencyPhone = \App\Tools\TextHelper::formatPhone($row['Contact Person Primary Phone']);
		$member->rideJournal = 0;
		$member->verifiedEmail = 9;
		$member->journal = 0;

		if ('true' == $row['Weekly Ride Schedule Email'])
			{
			$member->rideJournal = 1;
			$member->journal = 1;
			}

		$levels = \explode(',', $row['Ride Levels of Interest']);

		foreach ($levels as $level)
			{
			$parts = \explode(' ', $level);
			$categoryId = $categories[\array_shift($parts)] ?? 0;

			if ($categoryId)
				{
				$memberCategory = new \App\Record\MemberCategory();
				$memberCategory->categoryId = $categoryId;
				$memberCategory->member = $member;
				$memberCategory->insert();
				}
			}

		$userPermission = new \App\Record\UserPermission();
		$userPermission->member = $member;
		$userPermission->permissionGroup = 6; // normal member
		$userPermission->insert();

		$parts = \explode(',', $row['Club Roles']);

		foreach ($parts as $part)
			{
			if (isset($permissions[$part]))
				{
				$userPermission = new \App\Record\UserPermission();
				$userPermission->member = $member;
				$userPermission->permissionGroup = $permissions[$part];
				$userPermission->insert();
				}
			}
		}
	}

// add in payment details
$memberReader = \getReader('Member Reg Rpt (New).csv');

foreach ($memberships as $row)
	{
	if (\str_starts_with($row['Payment'] ?? '', 'Paypal-WPS'))
		{
		$email = \cleanEmail($row['Email *']);

		$member = new \App\Record\Member();
		$member->read(['email' => $email]);

		if ($member->loaded())
			{
			$payment = new \App\Record\Payment();
			$payment->amount = (float)$row['Registration Amount'];
			$payment->dateReceived = \date('Y-m-d', $row['renewedAt']);
			$payment->enteringMemberNumber = $member->memberId;
			$payment->membershipId = $member->membershipId;
			$payment->paymentDated = \makeDate($payment->dateReceived);
			$payment->paymentNumber = \substr($row['Payment'], 11);
			$payment->paymentType = 3;
			$payment->insert();
			}
		}
	}

/**
 * special events
 *
 * 'additionalInfo' => ['blob', 'string', 0, true, '', false, ],
 * 'checks' => ['int(1)', 'int', 1, true, 0, false, ],
 * 'directionsUrl' => ['varchar(100)', 'string', 100, true, '', false, ],
 * 'door' => ['int(1)', 'int', 1, true, 0, false, ],
 * 'endTime' => ['varchar(20)', 'string', 20, true, '', false, ],
 * 'eventDate' => ['date', 'string', 10, false, '', false, ],
 * 'eventId' => ['int(11)', 'int', 11, false, 0, true, ],
 * 'information' => ['blob', 'string', 0, true, '', false, ],
 * 'lastRegistrationDate' => ['date', 'string', 10, false, '', false, ],
 * 'location' => ['varchar(250)', 'string', 250, true, '', false, ],
 * 'maxDiscounts' => ['int(6)', 'int', 6, false, 0, false, ],
 * 'maxReservations' => ['int(6)', 'int', 6, true, 0, false, ],
 * 'membersOnly' => ['int(1)', 'int', 1, true, 0, false, ],
 * 'newMemberDate' => ['date', 'string', 10, true, '', false, ],
 * 'newMemberDiscount' => ['decimal(6,2)', 'float', 6, false, 0, false, ],
 * 'numberReservations' => ['int(2)', 'int', 2, true, 0, false, ],
 * 'organizer' => ['int(6)', 'int', 6, true, 0, false, ],
 * 'paypal' => ['int(1)', 'int', 1, true, 0, false, ],
 * 'price' => ['decimal(6,2)', 'float', 6, false, 0, false, ],
 * 'publicDate' => ['date', 'string', 10, true, '', false, ],
 * 'registrationStartDate' => ['date', 'string', 10, true, '', false, ],
 * 'startTime' => ['varchar(20)', 'string', 20, true, '', false, ],
 * 'title' => ['varchar(100)', 'string', 100, true, '', false, ],
 *
 * Event Date
 * Event Start Time
 * Event
 * Coordinator
 * Type
 * Event Description
 * URL of registration page
 * URL of registration page No Payment
 */
//$specialEventReader = \getReader('Special Events List.csv');
//
//$eventTitles = [];
//
//foreach ($specialEventReader as $row)
//	{
//	$event = new \App\Record\Event();
//
//	$event->eventDate = \makeDate($row['Event Date']);
//	$event->organizer = \getMember($row['Coordinator'])->memberId;
//	$event->startTime = $row['Event Start Time'];
//	$event->title = $row['Event'];
//	$event->paypal = 1;
//	$event->information = $row['Event Description'];
//	$eventTitles[$event->title] = $event->insert();
//	}
//
//$olderEventReader = \getReader('Special Events List.csv');
//
//foreach ($olderEventReader as $row)
//	{
//	$row['Title'] = $row['Title'] ?? 'Unknown';
//	$title = $row['Title'];
//
//	if (isset($eventTitles[$title]) || 'Member Registration' == $title)
//		{
//		continue;
//		}
//	$event = new \App\Record\Event();
//
//	$event->eventDate = \makeDate($row['Event Date']);
//	$event->title = $row['Title'];
//	$event->paypal = 1;
//	$event->information = $row['Event Description'];
//	$eventTitles[$event->title] = $event->insert();
//	}

$memberTable = new \App\Table\Member();
$eventRegistrations = \getReader('Event Reg (All Activity).csv');

foreach ($eventRegistrations as $row)
	{
	$eventId = $eventTitles[$row['Event']] ?? 0;

	if (! $eventId)
		{
		continue;
		}

	$condition = new \PHPFUI\ORM\Condition('firstName', $row['First Name']);
	$condition->and(new \PHPFUI\ORM\Condition('lastName', $row['Last Name']));
	$memberTable->setWhere($condition);

	$member = $memberTable->getRecordCursor()->current();

	if ($member->empty())
		{
		continue;
		}

	$paymentId = 0;
	$reservation = new \App\Record\Reservation();
	$reservation->address = \App\Tools\TextHelper::properCase($row['Address *']);
//	$reservation->comments = \App\Tools\TextHelper::properCase($row['']);
	$reservation->eventId = $eventId;
	$reservation->member = $member;

	if (\str_starts_with($row['Payment'], 'Paypal-WPS'))
		{
		$payment = new \App\Record\Payment();
		$payment->amount = (float)$row['Paid Total'];
		$payment->dateReceived = \date('Y-m-d', \strtotime($row['Time Added']));
		$payment->membershipId = $member->membershipId;
		$payment->paymentDated = $payment->dateReceived;
		$payment->paymentNumber = \substr($row['Payment'], 11);
		$payment->paymentType = 3;
		$reservation->payment = $payment;
		}

//	$reservation->phone = \App\Tools\TextHelper::properCase($row['']);
	$reservation->pricePaid = (float)$row['Registration Total'];
	$reservation->reservationFirstName = \App\Tools\TextHelper::properCase($row['First Name']);
	$reservation->reservationLastName = \App\Tools\TextHelper::properCase($row['Last Name']);
	$reservation->reservationemail = \cleanEmail($row['email'] ?? '');

	if (! empty($row['Time Added']))
		{
		$reservation->signedUpAt = \date('Y-m-d H:i:s', \strtotime($row['Time Added']));
		}
	$reservation->state = \App\Tools\TextHelper::properCase($row['State']);
	$reservation->town = \App\Tools\TextHelper::properCase($row['City *']);
	$reservation->zip = \App\Tools\TextHelper::properCase($row['Zip']);

	$reservationPerson = new \App\Record\ReservationPerson();
	$reservationPerson->email = $reservation->reservationemail;
	$reservationPerson->eventId = $eventId;
	$reservationPerson->firstName = $reservation->reservationFirstName;
	$reservationPerson->lastName = $reservation->reservationLastName;
	$reservationPerson->reservation = $reservation;
	$reservationPerson->insert();
	}

/**
 * ride schedule
 *
 * Ride Date
 * Day
 * Pace Time
 * Ride Name
 * Pace
 * Miles
 * Description
 * Start Location
 * Start Address
 * Ride Leader
 * Directions
 * Alt Start Location
 * Notes
 * Added Time
 * Last Modified User
 * Last Modified Time
 * ride_leader_name
 * JustPace
 */
$memberTable = new \App\Table\Member();

$startLocations = [];
$startLocationTable = new \App\Table\StartLocation();
$startLocationTable->delete(true);

$startLocationReader = \getReader('Ride Start Locations.csv');
$startLocations = [];

foreach ($startLocationReader as $row)
	{
	$startLocation = new \App\Record\StartLocation();
	$startLocation->name = $row['Start Location'];
	$startLocation->town = $row['Start Town'];
	$dom = new \voku\helper\HtmlDomParser($row['Google Map']);

	foreach ($dom->find('a') as $node)
		{
		$startLocation->link = \str_replace(' ', '+', \trim($node->getAttribute('href')));
		}
	$startLocation->address = $row['Street Address'];
	$startLocation->state = $row['State'];
	$startLocation->nearestExit = $row['Nearest Exit'];
	$startLocation->nearestExit = $row['Nearest Exit'];
	$startLocation->directions = $row['Directions'];
	$startLocations[$startLocation->name] = $startLocation->insert();
	}

$rideReader = \getReader('Ride Schedule List.csv');

foreach ($rideReader as $rideImport)
	{
	$ride = new \App\Record\Ride();
	$ride->mileage = $rideImport['Miles'];
	$ride->description = \trim($rideImport['Description'] . ' ' . $rideImport['Notes']);
	$ride->title = $rideImport['Ride Name'];
	$ride->rideDate = \App\Tools\Date::toString(\App\Tools\Date::fromString($rideImport['Ride Date'], 'mdy'));
	$parts = \explode(' ', $rideImport['Pace Time']);
	$time = \array_pop($parts);
	$time = \array_pop($parts) . ' ' . $time;
	$ride->startTime = \App\Tools\TimeHelper::toString(\App\Tools\TimeHelper::fromString($time));
	$ride->paceId = $paces[\trim($rideImport['JustPace'])];

	$ride->dateAdded = \date('Y-m-d H:i:s', \strtotime($rideImport['Added Time']));

	$leader = \explode(' ', $rideImport['Ride Leader']);
	$lastName = \array_pop($leader);
	$firstName = \implode(' ', $leader);
	$condition = new \PHPFUI\ORM\Condition('lastName', $lastName);
	$condition->and(new \PHPFUI\ORM\Condition('firstName', $firstName));

	$memberTable->setWhere($condition);
	$cursor = $memberTable->getRecordCursor();

	if ($cursor->count())
		{
		$ride->member = $cursor->current();
		}

	$ride->startLocationId = $startLocations[$rideImport['Start Location']] ?? null;

	$ride->insert();
	$rideSignup = new \App\Record\RideSignup();
	$rideSignup->member = $ride->member;
	$rideSignup->status = \App\Record\RideSignup::DEFINITELY_RIDING;
	$rideSignup->insert();
	}

$iterator = new \DirectoryIterator(__DIR__ . '/zoho/files_for_ridelibrary');

$files = [];

foreach ($iterator as $item)
	{
	if (! $item->isDir())
		{
		$files[$item->getFilename()] = $item->getPathname();
		}
	}

$csvReader = \getReader('Ride Library.csv');

$RWGPSIds = [];
$cueSheetTable = new \App\Table\CueSheet();
$cueSheetTable->delete(true);
$fields = ['PDF', 'Excel', 'Word', 'GPS'];

$model = new \App\Model\CueSheetFiles();
$cueSheetPath = $model->getPath();

$terrains = [
	'' => 0,
	'Flat' => 1,
	'Flat/ Rolling' => 2,
	'Rolling' => 3,
	'Rolling/ Hilly' => 4,
	'Hilly' => 5,
];

$cueSheetVersionTable = new \App\Table\CueSheetVersion();

foreach ($csvReader as $rideImport)
	{
	$dom = new \voku\helper\HtmlDomParser($rideImport['RidewithGPS Url']);

	$RWGPSId = ['RWGPSId' => 0];

	foreach ($dom->find('a') as $node)
		{
		if (false !== \strpos($node->href, 'ridewithgps'))
			{
			$RWGPSId = \App\Model\RideWithGPS::getRWGPSIdFromLink($node->href);
			}
		}
	unset($dom);
	$RWGPSId = $RWGPSId['RWGPSId'] ?? 0;
	$RWGPSIds[$RWGPSId] = null;
	$startLocationName = \trim($rideImport['Start location']);
	$startLocationId = $startLocations[$startLocationName] ?? null;
	$RWGPSIds[$RWGPSId] = $startLocationId;
	// add cue sheets
	$cuesheet = new \App\Record\CueSheet();
	$cuesheet->name = $rideImport['Ride Name'];
	$cuesheet->mileage = (float)$rideImport['Miles'];
	$cuesheet->startLocationId = $startLocationId;
	$cuesheet->RWGPSId = $RWGPSId;
  $cuesheet->pending = 0;
	$cuesheet->member = \getMember($rideImport['Cue Sheet By']);
	$cuesheet->dateAdded = \makeDate($rideImport['Revised Date']);
	$cuesheet->terrain = $terrains[$rideImport['Terrain']];
	$existingSheet = new \App\Record\CueSheet();

	if (! $existingSheet->read(['name' => $cuesheet->name, 'mileage' => $cuesheet->mileage, 'startLocationId' => $cuesheet->startLocationId]))
		{
		$cuesheet->insert();
		}
	else
		{
		$cuesheet = $existingSheet;
		$cueSheetVersionTable->setWhere(new \PHPFUI\ORM\Condition('cueSheetId', $cuesheet->cueSheetId));
		$cueSheetVersionTable->delete();
		}

	foreach ($fields as $fileField)
		{
		\addFile($cuesheet, $rideImport[$fileField], $cueSheetPath);
		}
	}

$rwgpsTable = new \App\Table\RWGPS();

foreach ($RWGPSIds as $rwgpsId => $startLocationId)
	{
	$rwgps = new \App\Record\RWGPS();
	$rwgps->RWGPSId = $rwgpsId;
	$rwgps->startLocationId = $startLocationId;
	$rwgps->insertOrUpdate();
	}

function addFile(\App\Record\CueSheet $cueSheet, string $file, string $path) : void
	{
	global $files;

	$file = \trim($file);

	if (empty($file))
		{
		return;
		}
	$parts = \explode('_', $file);
	$prefix = \array_shift($parts);

	$fileName = \implode('_', $parts);

	if (! isset($files[$fileName]))
		{
		echo "FILE NOT FOUND {$fileName}\n";

		return;
		}
	$importFile = $files[$fileName];

	$parts = \explode('.', $fileName);
	$extension = '.' . \array_pop($parts);

	$cueSheetVersion = new \App\Record\CueSheetVersion();
	$cueSheetVersion->cueSheet = $cueSheet;
	$cueSheetVersion->memberId = $cueSheet->memberId;
	$cueSheetVersion->dateAdded = $cueSheet->dateAdded;
	$cueSheetVersion->extension = $extension;
	$cueSheetVersion->insert();

	$destination = $path . $cueSheetVersion->cueSheetVersionId . $cueSheetVersion->extension;
	\copy($importFile, $destination);
	}


/*
Warning: Undefined array key "Street address" in C:\websites\BicycleClubWebsite2023\conversions\soundCyclistsCT\import.php on line 664
Deprecated: trim(): Passing null to parameter #1 ($string) of type string is deprecated in C:\websites\BicycleClubWebsite2023\conversions\soundCyclistsCT\import.php on line 664
Warning: Undefined array key "Directions" in C:\websites\BicycleClubWebsite2023\conversions\soundCyclistsCT\import.php on line 670
Deprecated: trim(): Passing null to parameter #1 ($string) of type string is deprecated in C:\websites\BicycleClubWebsite2023\conversions\soundCyclistsCT\import.php on line 670
Warning: Undefined array key "Description" in C:\websites\BicycleClubWebsite2023\conversions\soundCyclistsCT\import.php on line 697
Warning: Undefined array key "Notes" in C:\websites\BicycleClubWebsite2023\conversions\soundCyclistsCT\import.php on line 699
Warning: Undefined array key "Last Modified Time" in C:\websites\BicycleClubWebsite2023\conversions\soundCyclistsCT\import.php on line 706
makeDate(): Argument #1 ($mdyFormat) must be of type string, null given, called in C:\websites\BicycleClubWebsite2023\conversions\soundCyclistsCT\import.php on line 706;

 */
