<?php

namespace Tests\SQLite;

class GaPriceDateTest extends \Tests\SQLAsserts
	{
	public function testGetCurrentRegistrationRecord() : void
		{
		// parameters: \App\Record\GaEvent $event
		// test type: \App\Record\GaPriceDate
		// variables: $event

		$event = new \App\Record\GaEvent(46);

		$newTable = new \App\Table\GaPriceDate();
		$oldTable = new \Tests\Table\GaPriceDate();

		$this->setToMySQL();
		$expected = $oldTable->getCurrentRegistrationRecord($event);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getCurrentRegistrationRecord($event);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->toArray(), $actual->toArray(), __METHOD__);
		}

	public function testGetLastRegistrationRecord() : void
		{
		// parameters: \App\Record\GaEvent $event
		// test type: \App\Record\GaPriceDate
		// variables: $event

		$event = new \App\Record\GaEvent(46);

		$newTable = new \App\Table\GaPriceDate();
		$oldTable = new \Tests\Table\GaPriceDate();

		$this->setToMySQL();
		$expected = $oldTable->getLastRegistrationRecord($event);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getLastRegistrationRecord($event);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->toArray(), $actual->toArray(), __METHOD__);
		}
	}
