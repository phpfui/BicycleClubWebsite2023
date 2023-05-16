<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../common.php';

$tableObjects = \PHPFUI\ORM\Table::getAllTables();

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

echo "Generate Record Models\n\n";

\array_shift($argv);

$generator = new \PHPFUI\ORM\Tool\Generate\CRUD();

if (\count($argv))
	{
	foreach ($argv as $table)
		{
		if ($generator->generate($table))
			{
			echo "{$table}\n";
			}
		}

	exit;
	}

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
//		echo "{$table}\n";
		}
	}

\system('codestyle');

foreach ($tableObjects as $name => $table)
	{
	$parts = \explode('\\', $name);
	$class = \array_pop($parts);

	$phpFile = PROJECT_ROOT . '\\App\\Record\\Definition\\' . $class . '.php';
	$contents = \file_get_contents($phpFile);
	$class = \lcfirst($class);
	$contents = \str_replace(\strtolower($table->getTableName()), \lcfirst($class), $contents);
	$contents = \str_replace("'rWGPS", "'RWGPS", $contents);
	\file_put_contents($phpFile, $contents);
	}
