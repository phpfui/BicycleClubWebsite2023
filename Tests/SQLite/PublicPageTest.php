<?php

namespace Tests\SQLite;

class PublicPageTest extends \Tests\SQLAsserts
	{
	public function testSetDates() : void
		{
		// parameters:
		// test type: static
		// variables:


		$newTable = new \App\Table\PublicPage();
		$oldTable = new \Tests\Table\PublicPage();

		$this->setToMySQL();
		$expected = $oldTable->setDates()->getRecordCursor();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->setDates()->getRecordCursor();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}
	}
