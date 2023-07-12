<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

$migrate = new \PHPFUI\ORM\Migrator();

if ((int)($argv[2] ?? ''))
	{
	$migrate->migrateTo((int)$argv[1]);
	}
else
	{
	$migrate->migrate();
	}

\print_r($migrate->getErrors());
