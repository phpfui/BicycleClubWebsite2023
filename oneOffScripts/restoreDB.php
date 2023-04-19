<?php

function help(string $message = '') : void
	{
	if ($message)
		{
		echo "Error: {$message}\n\n";
		}
	echo "RestoreDB db <filename.gz> - Restore a datebase from a .gz file\n\n";
	echo "db is required database to restore into\n";
	echo "filename.gz is file to restore, default: backup.gz\n";
	echo "-help, -? for this text\n";

	exit;
	}

$help = ['-help', '-?'];

foreach ($argv as $arg)
	{
	if (\in_array(\strtolower($arg), $help))
		{
		\help();
		}
	}
// set the server name which determains which db to use
$db = $argv[1] ?? '';
$_SERVER['SERVER_NAME'] = $db;

include __DIR__ . '/../common.php';

if ($dbSettings->empty())
	{
	\help("Database {$db} was not found");
	}

$fileName = $argv[2] ?? 'backup.gz';

if (! \file_exists($fileName))
	{
	\help("Backup file {$fileName} was not found");
	}

echo 'Backup is dated ' . \date('F d Y H:i:s.', \filemtime($fileName)) . "\n";

// Raising this value may increase performance
$bufferSize = 4096 * 8; // read 4kb at a time
$outFileName = \str_replace('.gz', '', $fileName);

// Open our files (in binary mode)
$file = \gzopen($fileName, 'rb');
$outFile = \fopen($outFileName, 'wb');

// Keep repeating until the end of the input file
while(! \gzeof($file)) {
	// Read buffer-size bytes
	// Both fwrite and gzread and binary-safe
	\fwrite($outFile, \gzread($file, $bufferSize));
}

// Files are done, close files
\fclose($outFile);
\gzclose($file);

$restoredFileName = "backup.{$db}.sql";
$cleaner = new \App\Tools\CleanBackup('backup', $restoredFileName);
$cleaner->run();

echo "Restoring backup\n";

$restore = new \App\Model\Restore($restoredFileName);
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
