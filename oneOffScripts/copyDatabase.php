<?php

include 'commonbase.php';

function trans(string $text, array $parameters = []) : string
	{
	return \PHPFUI\Translation\Translator::trans($text, $parameters);
	}

\App\Tools\SessionManager::start();

\PHPFUI\Translation\Translator::setTranslationDirectory(PROJECT_ROOT . '/languages/installed');

$from = new \App\Settings\DB($argv[1] ?? '');

if ($from->empty())
	{
	echo "From database is empty\n";

	exit();
	}

$fromConnection = \PHPFUI\ORM::addConnection($from->getPDO());
\PHPFUI\ORM::setLogger(new \PHPFUI\ORM\StandardErrorLogger());

$to = new \App\Settings\DB($argv[2] ?? '');

if ($to->empty())
	{
	echo "To database is empty\n";

	exit();
	}

if ($to->host == $from->host && $to->dbname == $from->dbname)
	{
	echo "From and To databases are the same\n";

	exit();
	}

echo "Ready to copy from {$from->host}::{$from->dbname} to {$to->host}::{$to->dbname}\n";
echo "Enter Y to continue\n";
$input = \strtoupper(\fgets(STDIN));

if (! \str_starts_with($input, 'Y'))
	{
	echo "Aborted\n";

	exit();
	}

$cursors = [];

foreach (\PHPFUI\ORM\Table::getAllTables([]) as $table)
	{
	$cursors[$table->getTableName()] = $table->getRecordCursor();
	}

// set up a new database connection
$toConnection = \PHPFUI\ORM::addConnection($to->getPDO());

foreach ($cursors as $tableName => $cursor)
	{
	echo "Converting {$tableName} with {$cursor->total()} records\n";

	foreach ($cursor as $record)
		{
		$record->insert();	// insert into new database ($newConnectionId)
		}
	unset($cursors[$tableName]);
	$cursor = null;
	\gc_collect_cycles();
	}
