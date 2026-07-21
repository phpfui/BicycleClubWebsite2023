<?php

namespace Tests\SQLite;

class PollTest extends \Tests\SQLAsserts
	{
	public function testByYear() : void
		{
		// parameters: int $year
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $year

		$year = 2025;

		$newTable = new \App\Table\Poll();
		$oldTable = new \Tests\Table\Poll();

		$this->setToMySQL();
		$expected = $oldTable->byYear($year);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->byYear($year);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testCurrent() : void
		{
		// parameters:
		// test type: \PHPFUI\ORM\RecordCursor
		// variables:


		$newTable = new \App\Table\Poll();
		$oldTable = new \Tests\Table\Poll();

		$this->setToMySQL();
		$expected = $oldTable->current();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->current();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals(\count($expected), \count($actual), __METHOD__);
		}

	public function testFuture() : void
		{
		// parameters:
		// test type: \PHPFUI\ORM\RecordCursor
		// variables:


		$newTable = new \App\Table\Poll();
		$oldTable = new \Tests\Table\Poll();

		$this->setToMySQL();
		$expected = $oldTable->future();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->future();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals(\count($expected), \count($actual), __METHOD__);
		}

	public function testGetRequiredPolls() : void
		{
		// parameters:
		// test type: \PHPFUI\ORM\RecordCursor
		// variables:


		$newTable = new \App\Table\Poll();
		$oldTable = new \Tests\Table\Poll();

		$this->setToMySQL();
		$expected = $oldTable->getRequiredPolls();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getRequiredPolls();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals(\count($expected), \count($actual), __METHOD__);
		}

	public function testLatest() : void
		{
		// parameters:
		// test type: \App\Record\Poll
		// variables:


		$newTable = new \App\Table\Poll();
		$oldTable = new \Tests\Table\Poll();

		$this->setToMySQL();
		$expected = $oldTable->latest();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->latest();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->toArray(), $actual->toArray(), __METHOD__);
		}

	public function testMyPolls() : void
		{
		// parameters:
		// test type: \PHPFUI\ORM\RecordCursor
		// variables:


		$newTable = new \App\Table\Poll();
		$oldTable = new \Tests\Table\Poll();

		$this->setToMySQL();
		$expected = $oldTable->myPolls();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->myPolls();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals(\count($expected), \count($actual), __METHOD__);
		}

	public function testOldest() : void
		{
		// parameters:
		// test type: \App\Record\Poll
		// variables:


		$newTable = new \App\Table\Poll();
		$oldTable = new \Tests\Table\Poll();

		$this->setToMySQL();
		$expected = $oldTable->oldest();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->oldest();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->toArray(), $actual->toArray(), __METHOD__);
		}
	}
