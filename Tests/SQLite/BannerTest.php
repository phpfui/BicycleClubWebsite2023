<?php

namespace Tests\SQLite;

class BannerTest extends \Tests\SQLAsserts
	{
	public function testGetActiveRows() : void
		{
		// parameters:
		// test type: array
		// variables:


		$newTable = new \App\Table\Banner();
		$oldTable = new \Tests\Table\Banner();

		$this->setToMySQL();
		$expected = $oldTable->getActiveRows();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getActiveRows();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testGetOldest() : void
		{
		// parameters:
		// test type: \App\Record\Banner
		// variables:


		$newTable = new \App\Table\Banner();
		$oldTable = new \Tests\Table\Banner();

		$this->setToMySQL();
		$expected = $oldTable->getOldest();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getOldest();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->toArray(), $actual->toArray(), __METHOD__);
		}
	}
