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
$dataPurger->addExceptionTable(new \App\Table\Setting());
$dataPurger->addExceptionTable(new \App\Table\Blog());
$dataPurger->addExceptionTable(new \App\Table\Story());
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
			$member->membership = $membership;
			$member->insert();
			\addPermissions($member, $row['Roles']);
			++$insertedCount;
			}
		}

	return $insertedCount;
	}
