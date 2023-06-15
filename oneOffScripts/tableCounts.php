<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

echo "Record counts of all tables\n";

foreach (\PHPFUI\ORM\Table::getAllTables() as $table)
	{
	echo $table->getTableName() . ': ' . $table->count() . "\n";
	}
