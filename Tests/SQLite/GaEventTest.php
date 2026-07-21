<?php

namespace Tests\SQLite;

class GaEventTest extends \Tests\SQLAsserts
	{
	public function testGetCurrentEvents() : void
		{
		// parameters:
		// test type: \PHPFUI\ORM\RecordCursor
		// variables:


		$newTable = new \App\Table\GaEvent();
		$oldTable = new \Tests\Table\GaEvent();

		$this->setToMySQL();
		$expected = $oldTable->getCurrentEvents();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getCurrentEvents();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}
	}
