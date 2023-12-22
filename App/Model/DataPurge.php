<?php

namespace App\Model;

class DataPurge
	{
	/**
	 * @var array<string,array<\PHPFUI\ORM\Record>>
	 */
	private array $data = [];

	/**
	 * @var array<string,\PHPFUI\ORM\Table>
	 */
	private array $exceptionTables = [];

	public function addAllTables() : static
		{
		foreach (\PHPFUI\ORM\Table::getAllTables() as $table)
			{
			if ($table->count())
				{
				$this->addExceptionTable($table);
				}
			}

		return $this;
		}

	public function addExceptionTable(\PHPFUI\ORM\Table $table) : static
		{
		$this->exceptionTables[$table->getTableName()] = $table;

		return $this;
		}

	public function purge() : static
		{

		// save off records in each exception table
		foreach ($this->exceptionTables as $tableName => $table)
			{
			$this->data[$tableName] = [];

			foreach ($table->getRecordCursor() as $record)
				{
				$this->data[$tableName][] = clone $record;
				}
			}

		// drop all the tables, probably garbage
		$tables = \PHPFUI\ORM::getRows('show tables');

		foreach ($tables as $row)
			{
			$table = \array_pop($row);
			\PHPFUI\ORM::execute('drop table ' . $table);
			}

		// get a new copy of the db
		$restore = new \App\Model\Restore(PROJECT_ROOT . '/Initial.schema');

		if (! $restore->run())
			{
			\print_r($restore->getErrors());

			exit;
			}

		// run latest migrations
		$migrator = new \PHPFUI\ORM\Migrator();
		$migrator->migrate();

		$errors = $migrator->getErrors();

		if ($errors)
			{
			\print_r($errors);

			exit;
			}

		foreach ($this->exceptionTables as $tableName => $table)
			{
			foreach ($this->data[$tableName] as $record)
				{
				$record->insertOrIgnore();
				}
			}

		$permissions = new \App\Model\Permission();

		$permissions->loadStandardPermissions();

		$addBruce = new \App\Cron\Job\AddBruce(new \App\Cron\Controller(5));
		$addBruce->run();

		return $this;
		}

	public function removeExceptionTable(\PHPFUI\ORM\Table $table) : static
		{
		unset($this->exceptionTables[$table->getTableName()]);

		return $this;
		}
	}
