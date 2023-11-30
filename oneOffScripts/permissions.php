<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

$permissionNameTable = new \App\Table\PermissionName();
$permissionNameTable->setWhere(new \PHPFUI\ORM\Condition('permissionId', new \PHPFUI\ORM\Operator\GreaterThan(), 10000));

$stringReader = new \App\Tools\CSV\FileReader('strings.csv');
$strings = [];

foreach ($stringReader as $row)
	{
	$string = $row['string'];
	$strings[$string] = $row['file'] ?? 'unknown';
	}

foreach ($permissionNameTable->getRecordCursor() as $permissionName)
	{
	$name = $permissionName->name;

	if (! isset($strings[$name]))
		{
		if (\str_ends_with($name, ' Email') || \str_ends_with($name, ' Messages') || \str_ends_with($name, ' Editor'))
			{
			continue;
			}
		\PHPFUI\ORM::execute('delete from permissionName where permissionId=?', [$permissionName->permissionId]);
		echo "Deleting {$name}\n";
		}
	}
