<?php

namespace Tests\SQLite;

class ReservationPersonTest extends \Tests\SQLAsserts
	{
	public function testGetNamesAlpha() : void
		{
		// parameters: \App\Record\Event $event
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $event

		$event = new \App\Record\Event(243);

		$newTable = new \App\Table\ReservationPerson();
		$oldTable = new \Tests\Table\ReservationPerson();

		$this->setToMySQL();
		$expected = $oldTable->getNamesAlpha($event);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getNamesAlpha($event);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}
	}
