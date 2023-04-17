<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

echo "Generate Validation Models\n\n";

\array_shift($argv);

$generator = new \PHPFUI\ORM\Tool\Generate\Validator();

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
		echo "{$table}\n";
		}
	}
