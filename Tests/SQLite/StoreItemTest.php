<?php

namespace Tests\SQLite;

class StoreItemTest extends \Tests\SQLAsserts
	{
	public function testByTitle() : void
		{
		// parameters: ?bool $hasVolunteerPoints = null, ?bool $activeOnly = null, ?bool $inStock = null, ?\App\Record\Folder $folder = null
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $hasVolunteerPoints, $activeOnly, $inStock, $folder

		$hasVolunteerPoints = true;
		$activeOnly = true;
		$inStock = false;
		$folder = null;

		$newTable = new \App\Table\StoreItem();
		$oldTable = new \Tests\Table\StoreItem();

		$this->setToMySQL();
		$expected = $oldTable->byTitle($hasVolunteerPoints, $activeOnly, $inStock, $folder);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->byTitle($hasVolunteerPoints, $activeOnly, $inStock, $folder);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetHighest() : void
		{
		// parameters:
		// test type: \App\Record\StoreItem
		// variables:


		$newTable = new \App\Table\StoreItem();
		$oldTable = new \Tests\Table\StoreItem();

		$this->setToMySQL();
		$expected = $oldTable->getHighest();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getHighest();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->toArray(), $actual->toArray(), __METHOD__);
		}
	}
