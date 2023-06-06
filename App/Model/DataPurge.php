<?php

namespace App\Model;

class DataPurge
	{
	private array $data = [];

	private array $tables = [];

	public function addExceptionTable(\PHPFUI\ORM\Table $table) : static
		{
		$this->tables[] = $table;

		return $this;
		}

	public function purge() : static
		{

		// save off records in each exception table
		foreach ($this->tables as $table)
			{
			$tableName = $table->getTableName();
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

		$permissions = new \App\Model\Permission();

		$permissions->loadStandardPermissions();

		// run latest migrations
		$migrator = new \PHPFUI\ORM\Migrator();
		$migrator->migrate();

		$errors = $migrator->getErrors();

		if ($errors)
			{
			\print_r($errors);

			exit;
			}

		foreach ($this->tables as $table)
			{
			$tableName = $table->getTableName();

			foreach ($this->data[$tableName] as $record)
				{
				$record->insertOrIgnore();
				}
			}

		$addBruce = new \App\Cron\Job\AddBruce(new \App\Cron\Controller(5));
		$addBruce->run();

		return $this;
		}
	}
