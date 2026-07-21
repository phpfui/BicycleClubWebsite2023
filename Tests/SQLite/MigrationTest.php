<?php

namespace Tests\SQLite;

class MigrationTest extends \Tests\SQLAsserts
	{
	public function testGetHighest() : void
		{
		// parameters:
		// test type: \App\Record\Migration
		// variables:


		$newTable = new \App\Table\Migration();
		$oldTable = new \Tests\Table\Migration();

		$this->setToMySQL();
		$expected = $oldTable->getHighest();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getHighest();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->toArray(), $actual->toArray(), __METHOD__);
		}
	}
