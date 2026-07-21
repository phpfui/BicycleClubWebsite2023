<?php

namespace Tests\SQLite;

class CueSheetTest extends \Tests\SQLAsserts
	{
	public function testGetCountByStartLocation() : void
		{
		// parameters:
		// test type: array
		// variables:


		$newTable = new \App\Table\CueSheet();
		$oldTable = new \Tests\Table\CueSheet();

		$this->setToMySQL();
		$expected = $oldTable->getCountByStartLocation();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getCountByStartLocation();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testGetCueSheetsForLocation() : void
		{
		// parameters: int $location
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $location

		$location = 593;

		$newTable = new \App\Table\CueSheet();
		$oldTable = new \Tests\Table\CueSheet();

		$this->setToMySQL();
		$expected = $oldTable->getCueSheetsForLocation($location);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getCueSheetsForLocation($location);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetForDateRange() : void
		{
		// parameters: string $startDate, string $endDate
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $startDate, $endDate

		$startDate = '2000-01-01';
		$endDate = '2025-01-01';

		$newTable = new \App\Table\CueSheet();
		$oldTable = new \Tests\Table\CueSheet();

		$this->setToMySQL();
		$expected = $oldTable->getForDateRange($startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getForDateRange($startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetForMemberDate() : void
		{
		// parameters: int $memberId, string $startDate, string $endDate
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $memberId, $startDate, $endDate

		$memberId = 2590;
		$startDate = '2000-01-01';
		$endDate = '2025-01-01';

		$newTable = new \App\Table\CueSheet();
		$oldTable = new \Tests\Table\CueSheet();

		$this->setToMySQL();
		$expected = $oldTable->getForMemberDate($memberId, $startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getForMemberDate($memberId, $startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testSetFromMemberCursor() : void
		{
		// parameters: int $memberId
		// test type: static
		// variables: $memberId

		$memberId = 2590;

		$newTable = new \App\Table\CueSheet();
		$oldTable = new \Tests\Table\CueSheet();

		$this->setToMySQL();
		$expected = $oldTable->setFromMemberCursor($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->setFromMemberCursor($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->getRecordCursor(), $actual->getRecordCursor(), __METHOD__);
		}

	public function testSetPendingCursor() : void
		{
		// parameters:
		// test type: static
		// variables:


		$newTable = new \App\Table\CueSheet();
		$oldTable = new \Tests\Table\CueSheet();

		$this->setToMySQL();
		$expected = $oldTable->setPendingCursor();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->setPendingCursor();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->getRecordCursor(), $actual->getRecordCursor(), __METHOD__);
		}

	public function testSetRecentlyAddedCursor() : void
		{
		// parameters: int $limit = 10
		// test type: static
		// variables: $limit

		$limit = 20;

		$newTable = new \App\Table\CueSheet();
		$oldTable = new \Tests\Table\CueSheet();

		$this->setToMySQL();
		$expected = $oldTable->setRecentlyAddedCursor($limit);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->setRecentlyAddedCursor($limit);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->getRecordCursor(), $actual->getRecordCursor(), __METHOD__);
		}
	}
