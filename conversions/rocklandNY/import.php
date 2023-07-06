<pre>
<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

echo '<pre>';

$membershipsTable = new \App\CRUD\Memberships();
$membersTable = new \App\CRUD\Members();

$csv = new \App\Tools\CSVReader('../2018-11-15 Members Rockland Bicycling Club.csv');

$permissions = new \App\Model\DBPermissions();

$memberships = [];
$members = [];

$id = 0;

foreach ($csv as $row)
	{
	foreach ($row as &$value)
		{
		$value = \ucwords(\strtolower($value));
		}

	if (empty($row['Member bundle ID or email']))
		{
		$row['Member bundle ID or email'] = $row['e-Mail'];
		}
	$memberships[$row['Member bundle ID or email']][] = $id;
	$members[$id++] = $row;
	}

$membershipMapping = ['address' => 'Address',
	'town' => 'City',
	'zip' => 'Postal code',
	'state' => 'Province/State',
];

$memberMapping = ['firstName' => 'First name',
	'lastName' => 'Last name',
	'email' => 'e-Mail',
	'phone' => 'Phone',
];

foreach ($memberships as $ids)
	{
	$membership = $members[$ids[0]];

	foreach ($membershipMapping as $dbField => $excelField)
		{
		$membership[$dbField] = $membership[$excelField];
		}
	$membership['pending'] = '';
	$expires = \unixtojd(\strtotime($membership['Renewal due']));

	if (! $expires)
		{
		$expires = \Tools\Date::today();
		}
	$month = \Tools\Date::month($expires);
	$year = \Tools\Date::year($expires);

	if (++$month > 12)
		{
		$month = 1;
		++$year;
		}
	$membership['expires'] = \Tools\Date::make($year, $month, 1) - 1;
	$membership['emailMembership'] = '';
	$membership['lastRenewed'] = \unixtojd(\strtotime($membership['Renewal date last changed']));
	$membership['joined'] = \unixtojd(\strtotime($membership['Member since']));
	$membership['type'] = '';
	$membership['state'] = \cleanState($membership['state']);
	$membership['allowedMembers'] = 0;
	$membership['affiliation'] = $membership['How Did You Hear About Us?'];

	$existing = $membershipsTable->read(['address' => $membership['address'], 'town' => $membership['town'], 'zip' => $membership['zip']]);

	if ($existing)
		{
		$membership['membershipId'] = $membershipId = $existing['membershipId'];
		$membershipsTable->update($membership);
		}
	else
		{
		$membershipId = $membershipsTable->insert($membership);
		}

	$membershipId = $membershipsTable->insert($membership);

	foreach ($ids as $id)
		{
		$member = $members[$id];

		foreach ($memberMapping as $dbField => $excelField)
			{
			$member[$dbField] = $member[$excelField];
			}
		$member['email'] = \strtolower($member['email']);
		$member['phone'] = '';
		$member['lastLoginUnix'] = \strtotime($member['Last login']);
		$member['showNothing'] = '0';
		$member['showNoStreet'] = '0';
		$member['showNoTown'] = '0';
		$member['showNoPhone'] = '0';
		$member['verifiedEmail'] = 10;
		$member['emailAnnouncements'] = ($member['Subscribed to emails'] ?? 'yes') == 'yes';
		$member['volunteerPoints'] = 0;
		$member['eMailReminderDaysA'] = 1;
		$member['eMailReminderDaysB'] = 3;
		$member['acceptedWaiver'] = 0;
		$member['emergencyContact'] = '';
		$member['emergencyPhone'] = '';
		$member['cellPhone'] = \cleanPhone($member['Phone']);
		$member['journal'] = 'yes' == $member['Event announcements'];
		$member['pendingLeader'] = 0;
		$member['license'] = '';
		$member['deceased'] = 0;
		$member['rideComments'] = 1;
		$member['emailNewsletter'] = 1; //$member['Member emails and newsletters'] == 'yes';
		$member['membershipId'] = $membershipId;
		$existing = $membersTable->read(['email' => $member['email']]);

		if ($existing)
			{
			$memberId = $member['memberId'] = $existing['memberId'];
			$membersTable->update($member);
			}
		else
			{
			$memberId = $membersTable->insert($member);
			}
		echo "{$memberId}\n";

		if ('Account Administrator (Full Access)' == $row['Administration access'])
			{
			$permissions->addPermissionToUser($memberId, 'Super User');
			}
		elseif ('Event Manager' == $row['Administration access'])
			{
			$permissions->addPermissionToUser($memberId, 'Event Coordinator');
			}
		elseif ('Membership Manager' == $row['Administration access'])
			{
			$permissions->addPermissionToUser($memberId, 'Membership Chair');
			}
		$permissions->addPermissionToUser($memberId, 'Normal Member');
		}
	}

function cleanPhone(string $phone) : string
	{
	$phone = \preg_replace('/[^0-9]/', '', $phone);
	$area = \substr($phone, 0, 3);
	$exchange = \substr($phone, 3, 3);
	$number = \substr($phone, 6, 4);

	return "{$area}-{$exchange}-{$number}";
	}

function cleanState(string $state) : string
	{
	$state = \preg_replace('/[^A-Z]/', '', $state);

	return \substr($state, 0, 2);
	}
