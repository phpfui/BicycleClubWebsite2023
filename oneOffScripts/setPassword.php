<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

if (! \filter_var($argv[2] ?? '', FILTER_VALIDATE_EMAIL))
	{
	echo "invalid email\n";

	exit;
	}

$member = new \App\Record\Member(['email' => $argv[2]]);

if (! $member->loaded())
	{
	echo "Email address not found\n";
	}

$password = $argv[3] ?? '';

if (! $password)
	{
	echo "The third parameter must be the password\n";

	exit;
	}

$memberModel = new \App\Model\Member();

$member->password = $memberModel->hashPassword($password);
$member->update();

echo "Password updated\n";
