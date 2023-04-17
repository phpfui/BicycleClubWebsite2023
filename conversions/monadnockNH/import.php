<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

function cleanPhone(string $phone) : string
	{
	$phone = \preg_replace('/[^0-9]/', '', $phone);
	$area = \substr($phone, 0, 3);
	$exchange = \substr($phone, 3, 3);
	$number = \substr($phone, 6, 4);

	return "{$area}-{$exchange}-{$number}";
	}

$groupNames = [
	1 => 'Super User',
	2 => 'Normal Member',
	3 => 'Board Member',
	4 => 'Ride Leader',
];
$permissionNameTable = new \App\CRUD\PermissionName();

foreach ($groupNames as $id => $name)
	{
	$permissionName = $permissionNameTable->read($id);

	if (! $permissionName)
		{
		$permissionNameTable->insert(['name' => $name]);
		}
	}

\PHPFUI\ORM::execute('truncate table member');
\PHPFUI\ORM::execute('truncate table membership');

$membershipTable = new \App\CRUD\Membership();
$memberTable = new \App\CRUD\Member();

$csv = new \App\Tools\CSVReader($argv[1]);

$permissions = new \App\Model\DBPermissions();

$memberships = [];

foreach ($csv as $row)
	{
	$confirmNumber = $row['Confirmation Number'];
	$membershipNumber = $memberships[$confirmNumber] ?? 0;
	// add membership if not found
	if (! $membershipNumber)
		{
		$membership = $row;
		$address = $row['address'];
		$parts = \explode(' ', $address);
		$membership['zip'] = \array_pop($parts);

		if ($membership['address2'])
			{
			$membership['address'] .= ', ' . $membership['address2'];
			}
		$membership['pending'] = 0;
		$membership['expires'] = \App\Tools\Date::make(2021, 12, 31);
		$membership['joined'] = \App\Tools\Date::make(2021, 1, 1);
		$membership['allowedMembers'] = 'Individual Member' == $row['Category Entered'];
		$membershipNumber = $membershipTable->insert($membership);
		$memberships[$confirmNumber] = $membershipNumber;
		}

	$member = $row;
	$member['acceptedWaiver'] = 0;
	$member['allowTexting'] = 1;
	$member['cellPhone'] = \cleanPhone($member['cellPhone']);
	$member['deceased'] = 0;
	$member['discountCount'] = 0;
	$member['email'] = \strtolower($member['email']);
	$member['emailAnnouncements'] = 1;
	$member['emailNewsletter'] = 1;
	$member['emergencyPhone'] = \cleanPhone($member['emergencyPhone']);
	$member['extension'] = '';
	$member['geoLocate'] = 1;
	$member['journal'] = 1;
	$member['lastLoginUnix'] = 0;
	$member['leaderPoints'] = 0;
	$member['license'] = '';
	$member['loginAttempts'] = '';
	$member['membershipNumber'] = $membershipNumber;
	$member['newRideEmail'] = 1;
	$member['password'] = \mt_rand();
	$member['pendingLeader'] = 0;
	$member['phone'] = '';
	$member['profileHeight'] = 0;
	$member['profileWidth'] = 0;
	$member['profileX'] = 0;
	$member['profileY'] = 0;
	$member['rideComments'] = 1;
	$member['rideJournal'] = 1;
	$member['showNoPhone'] = '0';
	$member['showNoStreet'] = '0';
	$member['showNothing'] = '0';
	$member['showNoTown'] = '0';
	$member['verifiedEmail'] = 10;
	$memberId = $memberTable->insert($member, false);
	$permissions->addPermissionToUser($memberId, 'Normal Member');
	}

$membership['affiliation'] = 'Web Master';
$membership['town'] = 'Scarsdale';
$membership['state'] = 'NY';
$membership['address'] = '40 Chase Road';
$membership['zip'] = '10583';
$membership['pending'] = 0;
$membership['expires'] = \App\Tools\Date::make(2099, 12, 31);
$membership['joined'] = \App\Tools\Date::make(2021, 1, 1);
$membership['allowedMembers'] = 0;
$membershipNumber = $membershipTable->insert($membership);

$model = new \App\Model\Member();
$member['acceptedWaiver'] = 0;
$member['allowTexting'] = 1;
$member['cellPhone'] = \cleanPhone('914-361-9059');
$member['deceased'] = 0;
$member['discountCount'] = 0;
$member['email'] = 'brucekwells@gmail.com';
$member['emailAnnouncements'] = 1;
$member['emailNewsletter'] = 1;
$member['emergencyContact'] = 'Anne Hintermeister';
$member['emergencyPhone'] = \cleanPhone('914-714-3866');
$member['extension'] = '';
$member['firstName'] = 'Bruce';
$member['geoLocate'] = 1;
$member['journal'] = 1;
$member['lastLoginUnix'] = 0;
$member['lastName'] = 'Wells';
$member['leaderPoints'] = 0;
$member['license'] = '';
$member['loginAttempts'] = '';
$member['membershipNumber'] = $membershipNumber;
$member['newRideEmail'] = 1;
$member['password'] = $model->hashPassword($argv[2] ?? 'Password1234');
$member['pendingLeader'] = 0;
$member['phone'] = \cleanPhone('914-472-2696');
$member['profileHeight'] = 0;
$member['profileWidth'] = 0;
$member['profileX'] = 0;
$member['profileY'] = 0;
$member['rideComments'] = 1;
$member['rideJournal'] = 1;
$member['showNoPhone'] = '0';
$member['showNoStreet'] = '0';
$member['showNothing'] = '0';
$member['showNoTown'] = '0';
$member['verifiedEmail'] = 10;

$memberId = $memberTable->insert($member, false);
$permissions->addPermissionToUser($memberId, 'Super User');
