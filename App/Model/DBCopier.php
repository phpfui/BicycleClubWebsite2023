<?php

namespace App\Model;

class DBCopier
	{
	public readonly \PHPFUI\ORM\Interface\PDOInstance $fromPDO;

	public function __construct(private \App\Settings\DB $from)
		{
		$this->fromPDO = $from->getPDO();
		}

	public function copyTable(\PHPFUI\ORM\Table $table, \App\Settings\DB $to) : void
		{
		// get the current connection to restore later
		$currentConnection = \PHPFUI\ORM::getConnection();

		$recordCursor = $table->getRecordCursor();

		if (\count($recordCursor))
			{
			$newConnectionId = \PHPFUI\ORM::addConnection($to->getPDO(), __CLASS__);
			\PHPFUI\ORM::useConnection($newConnectionId);
			$table->insert($recordCursor, insertAutoIncrementKey: true);

			// back to the original database
			\PHPFUI\ORM::useConnection($currentConnection);
			}
		}

	public function copyTables(\App\Settings\DB $to) : void
		{
		$this->createTables($to);

		foreach (\PHPFUI\ORM\Table::getAllTables() as $table)
			{
			$this->copyTable($table, $to);
			}
		}

	public function createTables(\App\Settings\DB $to) : void
		{
		$toPDO = $to->getPDO();

		foreach (\PHPFUI\ORM\Table::getAllTables() as $table)
			{
			$toPDO->execute("DROP TABLE IF EXISTS `{$table->getTableName()}`;");

			if ($toPDO->getPostGre())
				{
				$sql = $this->getCreatePostGreTable($table);
				}
			elseif ($toPDO->getSqlite())
				{
				$sql = $this->getCreateSqliteTable($table);
				}
			else
				{
				$sql = $this->getCreateMySQLTable($table);
				}
			$toPDO->execute($sql);
			}
		}

	public function getCreateMySQLTable(\PHPFUI\ORM\Table $table) : string
		{
		$fields = $this->fromPDO->describeTable($table->getTableName());
//		$foreignKeys = $this->fromPDO->getForeignKeys($table->getTableName());

		$record = $table->getRecord();
		$primaryKeys = $record->getPrimaryKeys();
		$lines = [];

		foreach ($table->getFields() as $name => $definition)
			{
			$defaultValue = '';

			if ($record->getAutoIncrement() && 1 == \count($primaryKeys) && $name === $primaryKeys[0])
				{
				$defaultValue = ' AUTO_INCREMENT';
				}
			else
				{
				if (null !== $definition->defaultValue)
					{
					if (\in_array($definition->defaultValue, \PHPFUI\ORM\Record::getSQLDefaults()))
						{
						$defaultValue = " default {$definition->defaultValue}";
						}
					else
						{
						$defaultValue = " default '{$definition->defaultValue}'";
						}
					}
				}
			$nullable = $definition->nullable ? '' : ' NOT NULL';
			$collate = false !== \stripos($definition->sqlType, 'text') || false !== \stripos($definition->sqlType, 'char') ? ' COLLATE utf8mb4_general_ci' : '';

			$lines[] = "\t`{$name}` " . $definition->sqlType . $collate . $nullable . $defaultValue;
			}

		if (\count($primaryKeys))
			{
			$lines[] = "\tPRIMARY KEY(`" . \implode('`, `', $primaryKeys) . '`)';
			}

		$tableSQL = "create table `{$table->getTableName()}` (\n" . \implode(",\n", $lines) . "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;\n";

		return $tableSQL . $this->createIndexes($table->getTableName(), $primaryKeys);
		}

	public function getCreatePostGreTable(\PHPFUI\ORM\Table $table) : string
		{
		$fields = $this->fromPDO->describeTable($table->getTableName());
//		$foreignKeys = $this->fromPDO->getForeignKeys($table->getTableName());

		$record = $table->getRecord();
		$primaryKeys = $record->getPrimaryKeys();
		$lines = [];

		foreach ($table->getFields() as $name => $definition)
			{
			$defaultValue = '';

			if ($record->getAutoIncrement() && 1 == \count($primaryKeys) && $name === $primaryKeys[0])
				{
				$defaultValue = ' SERIAL';
				\array_shift($primaryKeys);
				}
			else
				{
				if (null !== $definition->defaultValue)
					{
					if (\in_array($definition->defaultValue, \PHPFUI\ORM\Record::getSQLDefaults()))
						{
						$defaultValue = " default {$definition->defaultValue}";
						}
					else
						{
						$defaultValue = " default '{$definition->defaultValue}'";
						}
					}
				}
			$nullable = $definition->nullable ? '' : ' NOT NULL';

			$lines[] = "\t`{$name}` " . $this->getPostGreType($definition->sqlType) . $nullable . $defaultValue;
			}

		if (\count($primaryKeys) > 1)
			{
			$lines[] = "\tPRIMARY KEY(`" . \implode('`, `', $primaryKeys) . '`)';
			}

		$tableSQL = "create table `{$table->getTableName()}` (\n" . \implode(",\n", $lines) . "\n);\n";

		return $tableSQL . $this->createIndexes($table->getTableName(), $primaryKeys);
		}

	public function getCreateSqliteTable(\PHPFUI\ORM\Table $table) : string
		{
		$fields = $this->fromPDO->describeTable($table->getTableName());
//		$foreignKeys = $this->fromPDO->getForeignKeys($table->getTableName());

		$record = $table->getRecord();
		$primaryKeys = $record->getPrimaryKeys();
		$lines = [];

		foreach ($table->getFields() as $name => $definition)
			{
			$defaultValue = '';

			if ($record->getAutoIncrement() && 1 == \count($primaryKeys) && $name === $primaryKeys[0])
				{
				$defaultValue = ' PRIMARY KEY AUTOINCREMENT';
				\array_shift($primaryKeys);
				}
			else
				{
				if (null !== $definition->defaultValue)
					{
					if (\in_array($definition->defaultValue, \PHPFUI\ORM\Record::getSQLDefaults()))
						{
						$defaultValue = " default {$definition->defaultValue}";
						}
					else
						{
						$defaultValue = " default '{$definition->defaultValue}'";
						}
					}
				}
			$nullable = $definition->nullable ? '' : ' NOT NULL';
			$type = $this->getSQLiteType($definition->sqlType);
			$collate = false !== \strpos($type, 'TEXT') ? ' COLLATE NOCASE' : '';

			$lines[] = "\t`{$name}` " . $type . $collate . $nullable . $defaultValue;
			}

		if (\count($primaryKeys))
			{
			$lines[] = "\tPRIMARY KEY(`" . \implode('`, `', $primaryKeys) . '`)';
			}

		$tableSQL = "create table `{$table->getTableName()}` (\n" . \implode(",\n", $lines) . "\n);\n";

		return $tableSQL . $this->createIndexes($table->getTableName(), $primaryKeys);
		}

	public static function getPostGreType(string $sqlType) : string
		{
		$length = \preg_replace('/[a-z]/', '', \strtolower($sqlType));
		$type = \preg_replace('/[^a-z]/', '', \strtolower($sqlType));

		switch ($type)
			{
			case 'tinyint':
			case 'mediumint':
			case 'int':
				return 'INTEGER';

			case 'double':
			case 'float':
				return 'double precision';

			case 'decimal':
				return 'NUMERIC' . $length;

			case 'binary':
			case 'varbinary':
			case 'tinyblob':
			case 'blob':
			case 'mediumblob':
			case 'longblob':
				return 'BYTEA';
			}

		return $sqlType;
		}

	public static function getSQLiteType(string $sqlType) : string
		{
		$type = \preg_replace('/[^a-z]/', '', \strtolower($sqlType));

		switch ($type)
			{
			case 'tinyint':
			case 'smallint':
			case 'mediumint':
			case 'int':
			case 'integer':
			case 'bigint':
			case 'bit':
				return 'INTEGER';

			case 'float':
			case 'double':
			case 'decimal':
			case 'numeric':
				return 'REAL';

			case 'binary':
			case 'varbinary':
			case 'tinyblob':
			case 'blob':
			case 'mediumblob':
			case 'longblob':
				return 'BLOB';
			}

		return 'TEXT';
		}

	/**
	 * @param array<string> $primaryKeys
	 */
	private function createIndexes(string $tableName, array $primaryKeys) : string
		{
		$sql = '';

		foreach ($this->fromPDO->getIndexes($tableName) as $index)
			{
			if (\count($primaryKeys) > 1 && $index->name !== \array_first($primaryKeys))
				{
				$sql .= "CREATE INDEX `{$tableName}_{$index->name}' ON `{$tableName}` (`{$index->name}`);\n";
				}
			}

		return $sql;
		}
	}
