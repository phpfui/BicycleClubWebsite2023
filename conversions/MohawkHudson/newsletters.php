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

$liveConnection = \getDatabase($argv[1] ?? '');
$model = new \App\Model\NewsletterFiles(new \App\Record\Newsletter());

foreach (\glob(__DIR__ . '/bikeabout/*.pdf') as $file)
	{
	$baseName = \basename($file);
	$parts = \explode(' ', $baseName);
	$date = \explode('-', $parts[0]);
	$double = 'double' == $parts[1] ?? '';
	$year = (int)\array_shift($date);
	$month = (int)\array_shift($date);

	$newsletter = new \App\Record\Newsletter();
	$julian = \App\Tools\Date::make($year, $month, 1);
	$newsletter->date = \App\Tools\Date::toString($julian);
	$newsletter->dateAdded = \App\Tools\Date::toString($julian);
	$newsletter->size = \filesize($file);
	$newsletter->insert();
	\copy($file, $model->get($newsletter->newsletterId . '.pdf'));

	if ($double)
		{
		$julian = \App\Tools\Date::fromString(\App\Tools\Date::increment($julian, 45));
		$julian = \App\Tools\Date::firstOfMonth($julian);
		$newsletter = new \App\Record\Newsletter();
		$newsletter->date = \App\Tools\Date::toString($julian);
		$newsletter->dateAdded = \App\Tools\Date::toString($julian);
		$newsletter->size = \filesize($file);
		$newsletter->insert();
		\copy($file, $model->get($newsletter->newsletterId . '.pdf'));
		}
	}
