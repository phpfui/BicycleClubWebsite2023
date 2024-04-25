<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include 'common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

$type = \App\Enum\Store\Type::EVENT;

if (enum_exists($type::class))
	{
	echo "Enum\n";
	echo $type->name . "\n";
	echo $type->value . "\n";
	}
