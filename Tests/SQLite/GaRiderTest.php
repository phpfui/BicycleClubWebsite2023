<?php

namespace Tests\SQLite;

class GaRiderTest extends \Tests\SQLAsserts
	{
	public function testGetEmailsForEvents() : void
		{
		// parameters: array $events, int $pending = 0
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $events, $pending

		$events = [44 => 1, 45 => 1, 46 => 1, 47 => 1];
		$pending = 0;

		$newTable = new \App\Table\GaRider();
		$oldTable = new \Tests\Table\GaRider();

		$this->setToMySQL();
		$expected = $oldTable->getEmailsForEvents($events, $pending);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getEmailsForEvents($events, $pending);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__, 'email');
		}

	public function testGetForEvents() : void
		{
		// parameters: array $events
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $events

		$events = [44, 45, 46, 47];

		$newTable = new \App\Table\GaRider();
		$oldTable = new \Tests\Table\GaRider();

		$this->assertNoSQLErrors(__METHOD__);
		$this->setToMySQL();
		$expected = $oldTable->getForEvents($events);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getForEvents($events);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__, 'gaRiderId');
		}

	public function testGetRiderCursor() : void
		{
		// parameters: \App\Record\GaEvent $event, int $paid = 1
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $event, $paid

		$event = new \App\Record\GaEvent(47);
		$paid = 1;

		$newTable = new \App\Table\GaRider();
		$oldTable = new \Tests\Table\GaRider();

		$this->setToMySQL();
		$expected = $oldTable->getRiderCursor($event, $paid);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getRiderCursor($event, $paid);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals(\count($expected), \count($actual));
		}

	public function testTotalRegistrants() : void
		{
		// parameters: \App\Record\GaEvent $event
		// test type: int
		// variables: $event

		$event = new \App\Record\GaEvent(47);

		$newTable = new \App\Table\GaRider();
		$oldTable = new \Tests\Table\GaRider();

		$this->setToMySQL();
		$expected = $oldTable->totalRegistrants($event);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->totalRegistrants($event);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}
	}
