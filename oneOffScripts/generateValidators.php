<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

echo "Generate Validation Models\n\n";

$generator = new \PHPFUI\ORM\Tool\Generate\Validator();

$tables = \PHPFUI\ORM::getTables();

if (! \count($tables))
	{
	echo "No tables found. Check your database configuration settings.\n";

	exit;
	}

foreach ($tables as $table)
	{
	if ($generator->generate($table))
		{
		echo "{$table}\n";
		}
	}

\system('codestyle');

$tableObjects = \PHPFUI\ORM\Table::getAllTables();

foreach ($tableObjects as $name => $table)
	{
	$parts = \explode('\\', $name);
	$class = \array_pop($parts);

	$phpFile = PROJECT_ROOT . '\\App\\Record\\Validation\\' . $class . '.php';
	$contents = \file_get_contents($phpFile);
	$class = \lcfirst($class);
	$contents = \str_replace(\strtolower($table->getTableName()), \lcfirst($class), $contents);
	$contents = \str_replace("'rWGPS", "'RWGPS", $contents);
	\file_put_contents($phpFile, $contents);
	}
