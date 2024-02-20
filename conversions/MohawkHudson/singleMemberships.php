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

$membershipTable = new \App\Table\Membership();
$membershipCursor = $membershipTable->getRecordCursor();

foreach ($membershipCursor as $membership)
	{
	$skip = true;
	foreach ($membership->MemberChildren as $member)
		{
		if (! $skip)
			{
			$newMembership = clone $membership;
			$newMembership->membershipId = 0;
			$newMembership->insert();
			$member->membership = $newMembership;
			$member->update();
			}
		$skip = false;
		}
	}

