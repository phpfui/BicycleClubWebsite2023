<?php

include __DIR__ . '/../../commonbase.php';


function help(string $message = '') : void
	{
	if ($message)
		{
		echo "Error: {$message}\n\n";
		}
	echo "Exiting\n\n";

	exit;
	}

function trans(string $text, array $parameters = []) : string
	{
	return \PHPFUI\Translation\Translator::trans($text, $parameters);
	}

function deleteFile(string $fileName) : void
	{
	if (! \unlink($fileName))
		{
		echo "Failed to delete file {$fileName}\n";
		}
	else
		{
		echo "Deleted file {$fileName}\n";
		}
	}

function getDatabase(string $server) : int
	{
	$dbSettings = new \App\Settings\DB($server);
	$pdo = $dbSettings->getPDO();

	if (! $pdo)
		{
		\PHPFUI\ORM::log(\Psr\Log\LogLevel::EMERGENCY, $dbSettings->getError());

		exit;
		}

	echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

	return \PHPFUI\ORM::addConnection($pdo);
	}

\PHPFUI\ORM::setLogger(new \PHPFUI\ORM\StandardErrorLogger());
\PHPFUI\ORM::setTranslationCallback([\PHPFUI\Translation\Translator::class, 'trans']);
\PHPFUI\Translation\Translator::setTranslationDirectory(PROJECT_ROOT . '/languages');
\PHPFUI\Translation\Translator::setLocale('en_US');
\date_default_timezone_set('America/New_York');

$liveConnection = \getDatabase($argv[1] ?? 'mohawk');
$drupalConnection = \getDatabase($argv[2] ?? 'drupal');

$fileName = $argv[3] ?? PROJECT_ROOT . '/www.mohawkhudsoncyclingclub.org.sql.gz';

if (\file_exists($fileName))
	{
	echo "Backup file {$fileName} is dated " . \date('F d Y H:i:s.', \filemtime($fileName)) . "\n";

	// Raising this value may increase performance
	$bufferSize = 4096 * 8; // read 4kb at a time
	$restoredFileName = 'backup.mhbc.sql';

	// Open our files (in binary mode)
	$file = \gzopen($fileName, 'rb');
	$outFile = \fopen($restoredFileName, 'wb');

	// Keep repeating until the end of the input file
	while(! \gzeof($file)) {
		// Read buffer-size bytes
		// Both fwrite and gzread and binary-safe
		\fwrite($outFile, \gzread($file, $bufferSize));
	}

	// Files are done, close files
	\fclose($outFile);
	\gzclose($file);

	\deleteFile($fileName);

	$cleanedFileName = 'cleaned.mhbc.sql';
	echo "Cleaning file {$restoredFileName} into file {$cleanedFileName}\n";
	$cleaner = new \PHPFUI\ORM\Tool\CleanBackup($restoredFileName, $cleanedFileName);
	$cleaner->run();

	echo "Restoring backup {$cleanedFileName}\n";

	$restore = new \App\Model\Restore($cleanedFileName);
	$restore->run();
	$errors = $restore->getErrors();

	if ($errors)
		{
		echo "Errors found\n\n";

		foreach ($errors as $error)
			{
			echo $error . "\n";
			}
		}
	else
		{
		echo "Backup restored with no errors\n";
		}
	\deleteFile($restoredFileName);
	\deleteFile($cleanedFileName);
	}
else
	{
	echo "Using current data\n";
	}

$keys = ['cell_phone', 'emergency_contact_phone', 'expiration_date', 'first_name', 'home_phone', 'last_name', 'license_plate', 'list_in_public_directory', 'private_email', 'user_management_notes', 'previous_expiration_date', 'legacy_date_changed', 'date_paid'];
$values = ['cellPhone', 'emergencyPhone', 'expires', 'firstName', 'phone', 'lastName', 'license', 'showNothing', 'email', 'affiliation', 'previous_expiration_date', 'legacy_date_changed', 'date_paid'];
$validFields = \array_combine($keys, $values);

$badTables = ['user__field_interests', 'user__field_mailchimp_subscription', 'user__field_mailing_address', 'user__field_membership_level', 'user__field_membership_order', 'user__field_membership_type',
	'user__field_mhcc_position', 'user__field_occupation', 'user__field_payment_status', 'user__field_prefix', 'user__field_suffix', 'user__field_user_media_picture', 'user__field_website'];

$sql = 'select ';
$sql .= "`user__field_mailing_address`.`field_mailing_address_administrative_area` as `state`,\n";
$sql .= "`user__field_mailing_address`.`field_mailing_address_locality` as `town`,\n";
$sql .= "`user__field_mailing_address`.`field_mailing_address_postal_code` as `zip`,\n";
$sql .= "`user__field_mailing_address`.`field_mailing_address_address_line1` as `address`,\n";
$sql .= "`user__field_mailing_address`.`field_mailing_address_address_line2` as `address2`,\n";
$sql .= "`users_field_data`.`uid` as memberId,\n";

$joins = "LEFT JOIN `user__field_mailing_address` ON users_field_data.uid = user__field_mailing_address.entity_id AND user__field_mailing_address.deleted = 0\n";
$joins .= "LEFT JOIN `user__roles` ON users_field_data.uid = user__roles.entity_id AND user__roles.deleted = 0\n";
$comma = '';

foreach (\PHPFUI\ORM::getTables() as $table)
	{
	$tablePrefix = 'user__field_';

	if (\str_starts_with($table, 'migrate'))
		{
		\PHPFUI\ORM::execute('drop table ' . $table);
		}

	if (\str_starts_with($table, $tablePrefix) && ! \in_array($table, $badTables))
		{
		$fieldStart = 'field_';
		$field = \substr($table, 6);
		$as = \substr($field, 6);

		if (! isset($validFields[$as]))
			{
			continue;
			}
		$as = $validFields[$as] ?? $as;
		$field .= '_value';
		$select = "{$comma}`{$table}`.`{$field}` as `{$as}`";
		$sql .= $select;
		$join = "LEFT JOIN `{$table}` ON `users_field_data`.`uid` = `{$table}`.`entity_id` AND `{$table}`.`deleted` = 0\n";
		$joins .= $join;
		$comma = ",\n";
		}
	}

$sql .= "\nFROM `users_field_data`\n" . $joins . "WHERE `user__roles`.`roles_target_id` = 'paid_member' AND `users_field_data`.`uid` > 1";

$drupalMemberCursor = \PHPFUI\ORM::getArrayCursor($sql);

$startLocationSQL = "SELECT `node_field_data`.`title` AS `name`,
`node__field_location_address`.`field_location_address_locality` AS `town`,
`node__field_location_address`.field_location_address_administrative_area as state,
`node__field_location_address`.field_location_address_address_line1 as address,
`node__field_location_address`.field_location_address_address_line2 as address2,
node_revision__field_location_links.field_location_links_uri as `link`,
node_revision__field_location_links.field_location_links_title As address3,
node_revision__body.body_value as directions,
`node_revision__field_location_address_geoloc`.field_location_address_geoloc_lat as latitude,
`node_revision__field_location_address_geoloc`.field_location_address_geoloc_lng as longitude,
`node_field_data`.`nid` AS `startLocationId`
FROM `node_field_data`
LEFT JOIN `node__field_location_address` ON node_field_data.nid = node__field_location_address.entity_id AND node__field_location_address.deleted = '0'
left join node_revision__field_location_links on node_field_data.nid=node_revision__field_location_links.entity_id
left join node_revision__field_location_address_geoloc on node_field_data.nid=node_revision__field_location_address_geoloc.entity_id
left join node_revision__body on node_field_data.nid=node_revision__body.entity_id
and node_revision__field_location_links.deleted=0
WHERE (`node_field_data`.`status` = '1') AND (`node_field_data`.`type` IN ('starting_location'))
ORDER BY `name` ASC";

$drupalStartLocationsCursor = \PHPFUI\ORM::getArrayCursor($startLocationSQL);
echo "Converting {$drupalStartLocationsCursor->count()} start locations\n";

$permissionMapping = [
	'administrator' => 'Super User',
	'board_director' => 'Board Member',
	'membership_admin' => 'Membership Chair',
	'paid_member' => 'Normal Member',
	'ride_admin' => 'Ride Coordinator',
	'ride_leader_unmoderated' => 'Ride Leader',
	'ride_report_admin' => 'Ride Chair',
	'site_admin_assistant' => 'Super User',
	'site_editor' => 'Content Editor',
];

// need start location and ride data

// switch to live data
\PHPFUI\ORM::useConnection($liveConnection);

$migrate = new \PHPFUI\ORM\Migrator();
$migrate->migrate();

$startLocationTable = new \App\Table\StartLocation();
$startLocationTable->setWhere(new \PHPFUI\ORM\Condition('startLocationId', 5, new \PHPFUI\ORM\Operator\LessThan()));
$startLocationTable->delete();

foreach ($drupalStartLocationsCursor as $startArray)
	{
	$startLocation = new \App\Record\StartLocation();

	if ($startArray['address2'])
		{
		$startArray['address'] .= ', ' . $startArray['address2'];
		}

	if ($startArray['address3'])
		{
		$startArray['address'] .= ', ' . $startArray['address3'];
		}
	$startLocation->setFrom($startArray);
	$startLocation->active = 1;
	$startLocation->insertOrUpdate();
	}

$memberTables = [];
$memberTables[] = new \App\Table\AdditionalEmail();
$memberTables[] = new \App\Table\AssistantLeader();
$memberTables[] = new \App\Table\AuditTrail();
$memberTables[] = new \App\Table\BoardMember();
$memberTables[] = new \App\Table\CartItem();
$memberTables[] = new \App\Table\CueSheet();
$memberTables[] = new \App\Table\CueSheetVersion();
$memberTables[] = new \App\Table\File();
$memberTables[] = new \App\Table\ForumMember();
$memberTables[] = new \App\Table\ForumMessage();
$memberTables[] = new \App\Table\GaRider();
$memberTables[] = new \App\Table\Invoice();
$memberTables[] = new \App\Table\JournalItem();
$memberTables[] = new \App\Table\MailItem();
$memberTables[] = new \App\Table\MailPiece();
$memberTables[] = new \App\Table\Member();
$memberTables[] = new \App\Table\MemberCategory();
$memberTables[] = new \App\Table\MemberNotice();
$memberTables[] = new \App\Table\MemberOfMonth();
$memberTables[] = new \App\Table\OauthUser();
$memberTables[] = new \App\Table\Photo();
$memberTables[] = new \App\Table\PhotoComment();
$memberTables[] = new \App\Table\PhotoTag();
$memberTables[] = new \App\Table\PointHistory();
$memberTables[] = new \App\Table\Poll();
$memberTables[] = new \App\Table\PollResponse();
$memberTables[] = new \App\Table\Reservation();
$memberTables[] = new \App\Table\Ride();
$memberTables[] = new \App\Table\RideComment();
$memberTables[] = new \App\Table\RideSignup();
$memberTables[] = new \App\Table\RWGPSAlternate();
$memberTables[] = new \App\Table\RWGPSComment();
$memberTables[] = new \App\Table\RWGPSRating();
$memberTables[] = new \App\Table\SigninSheet();
$memberTables[] = new \App\Table\Slide();
$memberTables[] = new \App\Table\SlideShow();
$memberTables[] = new \App\Table\StoreOrder();
$memberTables[] = new \App\Table\UserPermission();
$memberTables[] = new \App\Table\VolunteerJobShift();
$memberTables[] = new \App\Table\VolunteerPoint();
$memberTables[] = new \App\Table\VolunteerPollResponse();

$existingMembers = [];
$existingMembers[] = new \App\Record\Member(['email' => 'elivote@outlook.com']);
$existingMembers[] = new \App\Record\Member(['email' => 'fkelly12054@gmail.com']);
$today = \App\Tools\Date::todayString();

$permissions = new \App\Model\Permission();

echo "Converting {$drupalMemberCursor->count()} members\n";

foreach ($drupalMemberCursor as $memberArray)
	{
	$memberArray['email'] = \App\Model\Member::cleanEmail($memberArray['email']);

	$member = new \App\Record\Member(['email' => $memberArray['email']]);
	$membership = $member->membership;

	if ($member->memberId < 1000)
		{
		foreach ($existingMembers as $existing)
			{
			if ($existing->memberId == $member->memberId)
				{
				$condition = new \PHPFUI\ORM\Condition('memberId', $existing->memberId);

				foreach ($memberTables as $table)
					{
					$table->setWhere($condition);
					$table->update(['memberId' => $memberArray['memberId']]);
					}
				}
			}
		$member->delete();
		}
	$member = new \App\Record\Member();
	$member->setFrom($memberArray);
	$member->allowTexting = 1;
	$member->deceased = 0;
	$member->emailAnnouncements = 1;
	$member->emailNewsletter = 1;
	$member->geoLocate = 1;
	$member->journal = 1;
	$member->volunteerPoints = 0;
	$member->newRideEmail = 1;
	$member->pendingLeader = 0;
	$member->rideComments = 1;
	$member->rideJournal = 1;
	$member->showNoPhone = 0;
	$member->showNoStreet = 0;
	$member->showNoTown = 0;
	$member->showNothing = $member->showNothing ? 0 : 1;
	$member->verifiedEmail = 9;

	$dates = [];
	$dates[] = \App\Tools\Date::fromString($memberArray['previous_expiration_date'] ?? $today) - 365;
	$dates[] = \App\Tools\Date::fromString($memberArray['legacy_date_changed'] ?? $today);
	$dates[] = \App\Tools\Date::fromString($memberArray['date_paid'] ?? $today);
	$joined = \App\Tools\Date::today();

	foreach ($dates as $date)
		{
		$joined = \min($joined, $date);
		}
	$joinedString = \App\Tools\Date::toString($joined);

	if (empty($membership->joined) || $membership->joined > $joinedString)
		{
		$memberArray['joined'] = $joinedString;
		}
	else
		{
		$memberArray['joined'] = $membership->joined;
		}

	$membershipId = $membership->membershipId;

	$membership->setFrom($memberArray);
	$membership->insertOrUpdate();

	$member->membership = $membership;
	$member->insertOrUpdate();
	$permissions->addPermissionToUser($member->memberId, 'Normal Member');
	}

// add permissions to members

\PHPFUI\ORM::useConnection($drupalConnection);
$sql = 'SELECT entity_id,roles_target_id from user__roles where deleted=0 order by entity_id;';
$permissionCursor = \PHPFUI\ORM::getArrayCursor($sql);

echo "Converting {$permissionCursor->count()} permissions\n";

\PHPFUI\ORM::useConnection($liveConnection);

$permissionMapping = [
	'administrator' => 'Super User',
	'board_director' => 'Board Member',
	'membership_admin' => 'Membership Chair',
	'paid_member' => 'Normal Member',
	'ride_admin' => 'Ride Coordinator',
	'ride_leader_unmoderated' => 'Ride Leader',
	'ride_report_admin' => 'Ride Chair',
	'site_admin_assistant' => 'Super User',
	'site_editor' => 'Content Editor',
];

foreach ($permissionCursor as $permissionArray)
	{
	$permissions->addPermissionToUser($permissionArray['entity_id'], $permissionMapping[$permissionArray['roles_target_id']] ?? 'Normal Member');
	}
