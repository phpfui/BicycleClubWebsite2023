<?php

namespace Tests\SQLite;

class StartLocationTest extends \Tests\SQLAsserts
	{
	public function testGetAll() : void
		{
		// parameters: array $where = []
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $where

		$where = ['active' => 1];

		$newTable = new \App\Table\StartLocation();
		$oldTable = new \Tests\Table\StartLocation();

		$this->setToMySQL();
		$expected = $oldTable->getAll($where);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getAll($where);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetByName() : void
		{
		// parameters: string $name
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $name

		$name = 'Bedford Village Elementary School';

		$newTable = new \App\Table\StartLocation();
		$oldTable = new \Tests\Table\StartLocation();

		$this->setToMySQL();
		$expected = $oldTable->getByName($name);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getByName($name);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}
	}
