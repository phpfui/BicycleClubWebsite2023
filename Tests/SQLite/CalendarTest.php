<?php

namespace Tests\SQLite;

class CalendarTest extends \Tests\SQLAsserts
	{
	public function testGetPending() : void
		{
		// parameters:
		// test type: \PHPFUI\ORM\RecordCursor
		// variables:


		$newTable = new \App\Table\Calendar();
		$oldTable = new \Tests\Table\Calendar();

		$this->setToMySQL();
		$expected = $oldTable->getPending();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getPending();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals(\count($expected), \count($actual), __METHOD__);
		}
	}
