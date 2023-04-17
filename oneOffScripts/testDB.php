<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

$tables = \PHPFUI\ORM::getRows('show tables');

if (\PHPFUI\ORM::getLastErrors())
	{
	\print_r(\PHPFUI\ORM::getLastErrors());

	exit;
	}

foreach ($tables as $table)
	{
	echo \array_pop($table) . "\n";
	}

$settingTable = new \App\Table\Setting();

if (\PHPFUI\ORM::getLastErrors())
	{
	\print_r(\PHPFUI\ORM::getLastErrors());

	exit;
	}

try
	{
	foreach ($settingTable->getRecordCursor() as $record)
		{
		echo "{$record->name} => {$record->value}\n";
		}
	echo "\nLooks like the database connection is working\n";
	}
catch (\Throwable $e)
	{
	echo "\nLooks like the database connection is working\n\nBUT setting table does not yet exist.\n";
	\print_r($e->message);
	}
