<?php

namespace Tests\SQLite;

class SigninSheetRideTest extends \Tests\SQLAsserts
	{
	public function testRides() : void
		{
		// parameters: int $signinSheetId
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $signinSheetId

		$signinSheetId = 310;

		$newTable = new \App\Table\SigninSheetRide();
		$oldTable = new \Tests\Table\SigninSheetRide();

		$this->setToMySQL();
		$expected = $oldTable->rides($signinSheetId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->rides($signinSheetId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}
	}
