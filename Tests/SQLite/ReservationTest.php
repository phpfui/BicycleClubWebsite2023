<?php

namespace Tests\SQLite;

class ReservationTest extends \Tests\SQLAsserts
	{
	public function testGetEmails() : void
		{
		// parameters: int $eventId, int $unpaidOnly
		// test type: \PHPFUI\ORM\ArrayCursor
		// variables: $eventId, $unpaidOnly

		$eventId = 243;
		$unpaidOnly = 0;

		$newTable = new \App\Table\Reservation();
		$oldTable = new \Tests\Table\Reservation();

		$this->setToMySQL();
		$expected = $oldTable->getEmails($eventId, $unpaidOnly);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getEmails($eventId, $unpaidOnly);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertArrayCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetLatestReservation() : void
		{
		// parameters:
		// test type: \App\Record\Reservation
		// variables:


		$newTable = new \App\Table\Reservation();
		$oldTable = new \Tests\Table\Reservation();

		$this->setToMySQL();
		$expected = $oldTable->getLatestReservation();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getLatestReservation();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->toArray(), $actual->toArray(), __METHOD__);
		}

	public function testSetReservationsCursor() : void
		{
		// parameters: \App\Record\Event $event
		// test type: static
		// variables: $event

		$event = new \App\Record\Event(243);

		$newTable = new \App\Table\Reservation();
		$oldTable = new \Tests\Table\Reservation();

		$this->setToMySQL();
		$expected = $oldTable->setReservationsCursor($event);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->setReservationsCursor($event);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->getRecordCursor(), $actual->getRecordCursor(), __METHOD__);
		}
	}
