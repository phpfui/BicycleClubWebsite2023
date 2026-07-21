<?php

namespace Tests\SQLite;

class JobEventTest extends \Tests\SQLAsserts
	{
	public function testCopy() : void
		{
		// parameters: \App\Record\JobEvent $fromJobEvent, string $title, string $toDate
		// test type: void
		// variables: $fromJobEvent, $title, $toDate

		$fromJobEvent = new \App\Record\JobEvent(411);
		$title = 'Test Event';
		$toDate = \App\Tools\Date::todayString();

		$newTable = new \App\Table\JobEvent();
		$oldTable = new \Tests\Table\JobEvent();

		$this->setToMySQL();
		$expected = $oldTable->copy($fromJobEvent, $title, $toDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->copy($fromJobEvent, $title, $toDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testGetJobEvents() : void
		{
		// parameters: string $date = '1000-01-01'
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $date

		$date = '2026-06-17';

		$newTable = new \App\Table\JobEvent();
		$oldTable = new \Tests\Table\JobEvent();

		$this->setToMySQL();
		$expected = $oldTable->getJobEvents($date);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getJobEvents($date);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetJobEventsBetween() : void
		{
		// parameters: string $startDate, string $endDate
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $startDate, $endDate

		$startDate = '2025-01-01';
		$endDate = '2026-01-01';

		$newTable = new \App\Table\JobEvent();
		$oldTable = new \Tests\Table\JobEvent();

		$this->setToMySQL();
		$expected = $oldTable->getJobEventsBetween($startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getJobEventsBetween($startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetLatest() : void
		{
		// parameters:
		// test type: array
		// variables:


		$newTable = new \App\Table\JobEvent();
		$oldTable = new \Tests\Table\JobEvent();

		$this->setToMySQL();
		$expected = $oldTable->getLatest();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getLatest();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testGetOldest() : void
		{
		// parameters:
		// test type: array
		// variables:


		$newTable = new \App\Table\JobEvent();
		$oldTable = new \Tests\Table\JobEvent();

		$this->setToMySQL();
		$expected = $oldTable->getOldest();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getOldest();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}
	}
