<?php

include __DIR__ . '/../common.php';

$mysql = $pdo;
$mysql->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$mysql->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

// 2. Connect to / Create SQLite (Target)
$sqliteFile = __DIR__ . '/../Tests/data/db.sqlite';

// Delete old file if restarting
if (\file_exists($sqliteFile))
	{
	echo "Delete {$sqliteFile}\n";
	\unlink($sqliteFile);
	}

$sqlite = new PDO('sqlite:' . $sqliteFile);
$sqlite->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

// 3. Fetch all tables from MySQL
$tables = \PHPFUI\ORM\Table::getAllTables();

echo "Starting migration...\n";

foreach ($tables as $tableObject)
	{
	$table = $tableObject->getTableName();
	echo "Migrating table: {$table}...\n";

	// --- Create Table Schema ---
	// Fetch MySQL column definitions
	$columnsQuery = $mysql->query("DESCRIBE `{$table}`");
	$columns = $columnsQuery->fetchAll();

	$sqliteCols = [];
	$primaryKeys = [];

	foreach ($columns as $col)
		{
		$name = $col['Field'];
		$type = \strtolower($col['Type']);
		$null = 'YES' === $col['Null'] ? 'NULL' : 'NOT NULL';

		// Map MySQL types to SQLite's simplified type system
		if (false !== \strpos($type, 'int'))
			{
			$sqliteType = 'INTEGER';
			}
		elseif (false !== \strpos($type, 'char') || false !== \strpos($type, 'text') || false !== \strpos($type, 'blob'))
			{
			$sqliteType = 'TEXT COLLATE NOCASE';
			}
		elseif (false !== \strpos($type, 'float') || false !== \strpos($type, 'double'))
			{
			$sqliteType = 'REAL';
			}
		elseif (false !== \strpos($type, 'decimal'))
			{
			$sqliteType = $type;
			}
		else
			{
			$sqliteType = 'TEXT COLLATE NOCASE'; // Fallback for dates, enums, etc.
			}

		// Compute default value
		$fieldDefault = '';

		if (null !== $col['Default'])
			{
			$value = $col['Default'];

			if (! \is_numeric($value))
				{
				if ('CURRENT_TIMESTAMP' !== $value)
					{
					$value = "'{$value}'";
					}
				}

			$fieldDefault = ' DEFAULT ' . $value;
			}

		// Track Primary Keys
		if ('PRI' === $col['Key'])
			{
			// If it's a single auto-incrementing int, SQLite handles it natively like this:
			if ('INTEGER' === $sqliteType && false !== \strpos($col['Extra'], 'auto_increment'))
				{
				$sqliteType = 'INTEGER PRIMARY KEY';
				$null = '';
				$fieldDefault = 'AUTOINCREMENT';
				}
			else
				{
				$primaryKeys[] = "`{$name}`";
				}
			}

		$sqliteCols[] = "`{$name}` {$sqliteType} {$null}{$fieldDefault}";
		}

	// Handle composite primary keys if any exist
	if (! empty($primaryKeys) && \count($primaryKeys) > \count(\array_filter($sqliteCols, static fn ($c) => false !== \strpos($c, 'PRIMARY KEY'))))
		{
		$sqliteCols[] = 'PRIMARY KEY (' . \implode(', ', $primaryKeys) . ')';
		}

	// Create the table in SQLite
	$createTableSql = "CREATE TABLE `{$table}` (\n" . \implode(",\n", $sqliteCols) . "\n);";

	$indexes = $mysql->getIndexes($table);
	$compoundIndexes = [];

	if ($indexes)
		{
		foreach ($indexes as $index)
			{
			if (! $index->primaryKey)
				{
				if (! \array_key_exists($index->keyName, $compoundIndexes))
					{
					$compoundIndexes[$index->keyName] = [];
					}
				$compoundIndexes[$index->keyName][] = $index->name;
				}
			}
		}

	foreach ($compoundIndexes as $indexName => $fields)
		{
		$createTableSql .= "\nCREATE INDEX `{$table}_{$indexName}` ON `{$table}` (`" . \implode('`,`', $fields) . '`);';
		}

	$sqlite->exec($createTableSql);

	// --- Copy Data ---
	// Fetch data from MySQL
	$dataQuery = $mysql->query("SELECT * FROM `{$table}`");

	// Prepare SQLite insert statement dynamically
	if ($firstRow = $dataQuery->fetch())
		{
		$fields = \array_keys($firstRow);
		$placeholders = \implode(', ', \array_fill(0, \count($fields), '?'));
		$insertSql = "INSERT INTO `{$table}` (`" . \implode('`, `', $fields) . "`) VALUES ({$placeholders})";
		$insertStmt = $sqlite->prepare($insertSql);

		// Wrap data insertion in a transaction for a massive speed boost
		$sqlite->beginTransaction();

		do
			{
			$insertStmt->execute(\array_values($firstRow));
			}
		while ($firstRow = $dataQuery->fetch());

		$sqlite->commit();
		}
	}

echo "Migration complete! Saved to: {$sqliteFile}\n";
