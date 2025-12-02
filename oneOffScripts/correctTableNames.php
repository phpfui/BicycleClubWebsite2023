<?php

include 'common.php';

$tableObjects = \PHPFUI\ORM\Table::getAllTables();
echo \count($tableObjects);

$fileName = 'Initial.schema';
$schema = \file_get_contents($fileName);

foreach ($tableObjects as $table)
	{
	$tableName = $table->getTableName();
	$lcTableName = \strtolower($tableName);

	if ($lcTableName != $tableName)
		{
		echo $tableName . "\n";
		$schema = \str_replace($lcTableName, $tableName, $schema);
		}
	}

\file_put_contents($fileName, $schema);
