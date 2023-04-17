<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[2] ?? 'localhost';

include __DIR__ . '/../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

if (! isset($argv[1]))
	{
	echo "You must pass the file to import as the first parameter.\n";

	exit();
	}

$file = $argv[1];

if (! \file_exists($file))
	{
	echo "File {$file} was not found\n";

	exit();
	}

class loadBackup extends \PHPFUI\ORM\Migration
	{
	public function __construct()
		{
		$this->dropTables($this->getAllTables());
		}

	public function up() : bool
		{
		return true;
		}

	public function down() : bool
		{
		return true;
		}
	}

$dummy = new loadBackup();

$restore = new \App\Model\Restore($file);

if (! $restore->run())
	{
	foreach ($restore->getErrors() as $error)
		{
		echo $error . "\n";
		}
	}
else
	{
	echo "Success\n";
	}
