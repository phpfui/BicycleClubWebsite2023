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

$drupalConnection = \getDatabase($argv[1] ?? 'drupal');

$fileName = $argv[3] ?? PROJECT_ROOT . '/www.mohawkhudsoncyclingclub.org.sql.gz';

if (! \file_exists($fileName))
	{
	\help("Backup file {$fileName} was not found");
	}

echo 'Backup is dated ' . \date('F d Y H:i:s.', \filemtime($fileName)) . "\n";

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
\unlink($fileName);

$cleanedFileName = 'cleaned.mhbc.sql';
$cleaner = new \PHPFUI\ORM\Tool\CleanBackup($restoredFileName, $cleanedFileName);
$cleaner->run();
\unlink($restoredFileName);

echo "Restoring backup\n";

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

\unlink($cleanedFileName);
