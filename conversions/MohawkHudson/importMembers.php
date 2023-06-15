<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

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

function dataConvert(string $date) : string
	{
	if ($date)
		{
		$date = \date('Y-m-d', \strtotime($date));
		}

	return $date;
	}

$permissionMapping = ['Board Director' => 'Board Member',
	'Membership Admin' => 'Membership Chair',
	'Paid Member' => 'Normal Member',
	'Ride Admin' => 'Ride Coordinator',
	'Ride Leader Unmoderated' => 'Ride Leader',
	'Ride Report Admin' => 'Ride Chair',
	'Site Admin Assistant' => 'Super User',
	'Site Editor' => 'Content Editor',
];

function addPermissions(\App\Record\Member $member, string $optionsString) : void
	{
	global $permissions, $permissionMapping;
	$permissions->addPermissionToUser($member->memberId, 'Normal Member');
	$parts = \explode(',', $optionsString);

	foreach ($parts as $part)
		{
		$part = \trim($part);

		if (! $part)
			{
			continue;
			}

		if (isset($permissionMapping[$part]))
			{
			$permissions->addPermissionToUser($member->memberId, $permissionMapping[$part]);
			}
		}
	}


function addOptions(string $optionsString, array &$options) : void
	{
	$parts = \explode(',', $optionsString);

	foreach ($parts as $part)
		{
		$part = \trim($part);

		if (! $part)
			{
			continue;
			}

		if (! isset($options[$part]))
			{
			$options[$part] = 0;
			}
		++$options[$part];
		}
	}

function fixZip(string $zip) : string
	{
	if ($zip)
		{
		while (\strlen($zip) < 5)
			{
			$zip = '0' . $zip;
			}
		}

	return $zip;
	}

$states = ['New York' => 'NY', 'Massachusetts' => 'MA', 'Connecticut' => 'CT', 'South Carolina' => 'SC', 'Vermont' => 'VT'];

function getState(string $state) : string
	{
	global $states;

	if (isset($states[$state]))
		{
		return $states[$state];
		}

	echo "{$state} was not found\n";

	exit;
	}

$dataPurger = new \App\Model\DataPurge();
$dataPurger->addAllTables();
// All these tables have memberId, uncomment to delete all data in them
//$dataPurger->removeExceptionTable(new \App\Table\AdditionalEmail());
//$dataPurger->removeExceptionTable(new \App\Table\AssistantLeader());
//$dataPurger->removeExceptionTable(new \App\Table\BoardMember());
//$dataPurger->removeExceptionTable(new \App\Table\CartItem());
//$dataPurger->removeExceptionTable(new \App\Table\CueSheet());
//$dataPurger->removeExceptionTable(new \App\Table\CueSheetVersion());
//$dataPurger->removeExceptionTable(new \App\Table\File());
//$dataPurger->removeExceptionTable(new \App\Table\ForumMember());
//$dataPurger->removeExceptionTable(new \App\Table\ForumMessage());
//$dataPurger->removeExceptionTable(new \App\Table\GaRider());
//$dataPurger->removeExceptionTable(new \App\Table\Invoice());
//$dataPurger->removeExceptionTable(new \App\Table\JournalItem());
//$dataPurger->removeExceptionTable(new \App\Table\MailItem());
//$dataPurger->removeExceptionTable(new \App\Table\MailPiece());
$dataPurger->removeExceptionTable(new \App\Table\Member());
$dataPurger->removeExceptionTable(new \App\Table\MemberCategory());
//$dataPurger->removeExceptionTable(new \App\Table\MemberOfMonth());
$dataPurger->removeExceptionTable(new \App\Table\Membership());
//$dataPurger->removeExceptionTable(new \App\Table\OauthUser());
//$dataPurger->removeExceptionTable(new \App\Table\Photo());
//$dataPurger->removeExceptionTable(new \App\Table\PhotoComment());
//$dataPurger->removeExceptionTable(new \App\Table\PhotoTag());
//$dataPurger->removeExceptionTable(new \App\Table\PointHistory());
//$dataPurger->removeExceptionTable(new \App\Table\Poll());
//$dataPurger->removeExceptionTable(new \App\Table\PollResponse());
//$dataPurger->removeExceptionTable(new \App\Table\Reservation());
//$dataPurger->removeExceptionTable(new \App\Table\Ride());
//$dataPurger->removeExceptionTable(new \App\Table\RideComment());
//$dataPurger->removeExceptionTable(new \App\Table\RideSignup());
//$dataPurger->removeExceptionTable(new \App\Table\RWGPSAlternate());
//$dataPurger->removeExceptionTable(new \App\Table\RWGPSComment());
//$dataPurger->removeExceptionTable(new \App\Table\RWGPSRating());
//$dataPurger->removeExceptionTable(new \App\Table\SigninSheet());
//$dataPurger->removeExceptionTable(new \App\Table\Slide());
//$dataPurger->removeExceptionTable(new \App\Table\SlideShow());
//$dataPurger->removeExceptionTable(new \App\Table\StoreOrder());
//$dataPurger->removeExceptionTable(new \App\Table\UserPermission());
//$dataPurger->removeExceptionTable(new \App\Table\VolunteerJobShift());
//$dataPurger->removeExceptionTable(new \App\Table\VolunteerPoint());
//$dataPurger->removeExceptionTable(new \App\Table\VolunteerPollResponse());
$dataPurger->purge();

$permissions = new \App\Model\Permission();

foreach (\glob(__DIR__ . '/*.csv') as $file)
	{
	echo "Importing file: {$file}\n";
	$insertedCount = \insertMembers($file);
	echo "Imported {$insertedCount} members\n";
	}
echo "Done importing\n";

function insertMembers(string $csvName) : int
	{
	$membersByAddress = [];
	$roles = [];
	$positions = [];
	$members = new \App\Tools\CSVReader($csvName);

	foreach ($members as $row)
		{
		$address = $row['Street'];

		if (! empty($row['Street 2']))
			{
			$address .= ', ' . $row['Street 2'];
			}
		$address .= $row['Zip Code'];
		$address = \strtolower($address);

		foreach (['Date Paid', 'Use Created Date', 'Expiration Date', 'Previous Expiration Date'] as $field)
			{
			$row[$field] = \dataConvert($row[$field]);
			}

		if (! isset($membersByAddress[$address]))
			{
			$membersByAddress[$address] = [];
			}
		$membersByAddress[$address][] = $row;

		\addOptions($row['Roles'], $roles);
		\addOptions($row['MHCC Position'], $positions);
		}

//	ksort($roles);
//	ksort($positions);
//
//	print_r($roles);
//	print_r($positions);
//
	$insertedCount = 0;

	foreach ($membersByAddress as $members)
		{
		$membership = new \App\Record\Membership();
		$row = $members[0];
		$membership->address = $row['Street'];

		if (! empty($row['Street 2']))
			{
			$comma = ', ';

			if ($membership->address == (int)$membership->address)
				{
				$comma = ' ';
				}
			$membership->address .= $comma . $row['Street 2'];
			}
		$membership->town = $row['City'];
		$membership->zip = \fixZip($row['Zip Code']);
		$membership->state = \getState($row['State']);
		$membership->pending = 0;
		$membership->allowedMembers = \count($members);

		$expired = \App\Tools\Date::fromString($row['Expiration Date']);
		$expired = \App\Tools\Date::endOfMonth($expired);
		$membership->expires = \App\Tools\Date::toString($expired);
		$membership->joined = $row['Use Created Date'];
		$membership->lastRenewed = $row['Previous Expiration Date'];
		$membership->clean();

		$existingMembership = new \App\Record\Membership();

		if ($existingMembership->read(['zip' => $membership->zip, 'address' => $membership->address]))
			{
			$membership = clone $existingMembership;
			}

		foreach ($members as $row)
			{
			$member = new \App\Record\Member();
			$member->allowTexting = 1;
			$member->cellPhone = $row['Cell Phone'];
			$member->phone = $row['Home Phone'];
			$member->deceased = 0;
			$member->email = \cleanEmail($row['Email']);
			$member->emailAnnouncements = 1;
			$member->emailNewsletter = 1;
			$member->geoLocate = 1;
			$member->journal = 1;
			$member->leaderPoints = 0;
			$member->newRideEmail = 1;
			$member->pendingLeader = 0;
			$member->rideComments = 1;
			$member->rideJournal = 1;
			$member->showNoPhone = 0;
			$member->showNoStreet = 0;
			$member->showNoTown = 0;
			$member->showNothing = 0;
			$member->verifiedEmail = 9;
			$member->membership = $membership;
			$member->firstName = $row['First Name'];
			$member->lastName = $row['Last Name'];
			$member->clean();
			$existingMember = new \App\Record\Member();

			if ($existingMember->read(['firstName' => $member->firstName, 'lastName' => $member->lastName]))
				{
				$membership = clone $existingMembership;
				}

			$member->membership = $membership;
			$member->insertOrUpdate();
			\addPermissions($member, $row['Roles']);
			++$insertedCount;
			}
		}

	return $insertedCount;
	}
