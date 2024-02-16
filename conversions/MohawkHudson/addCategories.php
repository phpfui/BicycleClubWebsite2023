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

$memberCursor = new \App\Tools\CSV\FileReader('mbrs to be loaded into DB 2024-02-13.csv');

$categoryTable = new \App\Table\Category();
$categoryCursor = $categoryTable->getRecordCursor();
$memberCategories = [];

foreach ($memberCursor as $memberArray)
	{
	$email = \App\Model\Member::cleanEmail($memberArray['Email']);

	$member = new \App\Record\Member(['email' => $email]);

	if ($member->loaded())
		{
		foreach ($categoryCursor as $category)
			{
			$mc = new \App\Record\MemberCategory();
			$mc->member = $member;
			$mc->category = $category;
			$memberCategories[] = $mc;
			}
		}
	}

$memberCategoryTable = new \App\Table\MemberCategory();
$memberCategoryTable->insert($memberCategories, 'ignore ');
