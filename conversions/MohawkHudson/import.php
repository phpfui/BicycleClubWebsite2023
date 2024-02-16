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

function cleanPhone(string $phone) : string
	{
	return \trim(\ltrim($phone, '1'));
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

//echo cleanPhone('1 784-382-9876');
//echo "\n";
//exit;

\PHPFUI\ORM::setLogger(new \PHPFUI\ORM\StandardErrorLogger());
\PHPFUI\ORM::setTranslationCallback([\PHPFUI\Translation\Translator::class, 'trans']);
\PHPFUI\Translation\Translator::setTranslationDirectory(PROJECT_ROOT . '/languages');
\PHPFUI\Translation\Translator::setLocale('en_US');
\date_default_timezone_set('America/New_York');

$liveConnection = \getDatabase($argv[1] ?? 'mohawk');

$validFields = [
	'First Name' => 'firstName',
	'Last Name' => 'lastName',
	'Email' => 'email',
	'Home Phone' => 'phone',
	'Cell Phone' => 'cellPhone',
	'Street' => 'address',
	'Street 2' => 'street2',
	'City' => 'town',
	'State' => 'state',
	'Zip Code' => 'zip',
	'MHCC Position' => 'position',
	'Roles' => 'roles',
	'List in Public Directory' => 'showNothing',
	'Number of BikeAbout Copies to Print' => 'copies',
	'Date Paid' => 'datePaid',
	'Membership Order Status' => 'status',
	'Use Created Date' => 'joined',
	'Expiration Date' => 'expires',
	'Previous Expiration Date' => 'previous',
];

$permissions = new \App\Model\Permission();

$memberTable = new \App\Table\Member();
$membershipTable = new \App\Table\Membership();

echo 'Current number of members in DB ' . \count($memberTable) . "\n";
echo 'Current number of memberships in DB ' . \count($membershipTable) . "\n";

$categoryTable = new \App\Table\Category();
$categoryCursor = $categoryTable->getRecordCursor();
$memberCategories = [];

$memberCursor = new \App\Tools\CSV\FileReader('mbrs to be loaded into DB 2024-02-13.csv');

$count = 0;
$webmaster = new \App\Record\Member(['lastName' => 'Livote']);

foreach ($memberCursor as $memberArray)
	{
	$memberArray['email'] = \App\Model\Member::cleanEmail($memberArray['Email']);

	$member = new \App\Record\Member(['email' => $memberArray['email']]);

	if ($member->loaded())
		{
		\print_r($memberArray);

		continue;
		}
	++$count;
	$member = new \App\Record\Member();

	foreach ($validFields as $index => $field)
		{
		$memberArray[$field] = $memberArray[$index];
		}

	foreach (['joined', 'expires'] as $field)
		{
		$memberArray[$field] = \App\Tools\Date::toString(\App\Tools\Date::fromString($memberArray[$field], 'mdy'));
		}
	$memberArray['expires'] = \App\Tools\Date::toString(\App\Tools\Date::endOfMonth(\App\Tools\Date::fromString($memberArray['expires'])));
	$memberArray['showNothing'] = (int)('No' === $memberArray['showNothing']);
	$memberArray['phone'] = \cleanPhone($memberArray['phone']);
	$memberArray['cellPhone'] = \cleanPhone($memberArray['cellPhone']);
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
	$member->verifiedEmail = 9;

	$membership = new \App\Record\Membership();

	if ($memberArray['street2'] && $memberArray['street2'] != $memberArray['town'])
		{
		$memberArray['address'] .= ' ' . $memberArray['street2'];
		}
	$memberArray['address'] .= ' ' . $memberArray['street2'];
	$membership->setFrom($memberArray);
	$membership->pending = 0;
	$member->membership = $membership;
	$member->insert();

	foreach ($categoryCursor as $category)
		{
		$mc = new \App\Record\MemberCategory();
		$mc->member = $member;
		$mc->category = $category;
		$memberCategories[] = $mc;
		}

	$permissions->addPermissionToUser($member->memberId, 'Normal Member');
	$email = new \App\Tools\EMail();
	$email->setHtml(true);
	$email->setSubject('New Mohawk Hudson Bicyling Club Website');
	$email->setBody("Dear {$member->fullName()},<p>
You are receiving this email from the club's new website!
</p>
<p>
The URL is the same: <a href='https://www.mohawkhudsoncyclingclub.org'>www.mohawkhudsoncyclingclub.org</a>.
</p>
Your login id for the new site is your email address but the first time you login, <u><i>you must first set a new password</i></u>. Use the link below to reset your password (or click on <b>Forgot My Password</b>):
</p>
<a href='https://www.mohawkhudsoncyclingclub.org/Membership/forgotPassword'>https://www.mohawkhudsoncyclingclub.org/Membership/forgotPassword</a>
<p>
Enter your email address and if the email address is found in the database, you will be sent a password reset link. If you don't receive password reset email, look in your spam/junk folder and if it's not there, contact us.
</p>
<p>
If you have problems getting logged into the new website, reply to this email or click on <b>Contact Us</b> (found in both the menu on the left and the gray bar menu at the bottom of the page). In the <b>Contact Us</b> form, select <b>Webmaster</b> from the dropdown menu in the <b>To</b> box. Please briefly describe the problem (e.g., <i>\"I did not get the password reset email\").
</p>
<p>
Regards,
</p>
<p>
MHCC Board of Directors
</p>");
	$email->setToMember($member->toArray());
	$email->setFromMember($webmaster->toArray());

	$email->bulkSend();
	}

$memberCategoryTable = new \App\Table\MemberCategory();
$memberCategoryTable->insert($memberCategories, 'ignore ');

echo "Imported {$count} members\n";
echo 'Current number of members in DB ' . \count($memberTable) . "\n";
echo 'Current number of memberships in DB ' . \count($membershipTable) . "\n";

$condition = new \PHPFUI\ORM\Condition('phone', '1%', new \PHPFUI\ORM\Operator\Like());
$condition->or('cellPhone', '1%', new \PHPFUI\ORM\Operator\Like());
$memberTable->setWhere($condition);

foreach ($memberTable->getRecordCursor() as $member)
	{
	$member->phone = \cleanPhone($member->phone);
	$member->cellPhone = \cleanPhone($member->cellPhone);
	$member->update();
	}
