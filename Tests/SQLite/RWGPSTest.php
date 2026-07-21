<?php

namespace Tests\SQLite;

class RWGPSTest extends \Tests\SQLAsserts
	{
	public function testClosest() : void
		{
		// parameters: float $lat, float $long, int $limit = 1, float $distance = 0.5
		// test type: \PHPFUI\ORM\ArrayCursor
		// variables: $lat, $long, $limit, $distance

		$lat = 40.9960226;
		$long = -73.806616;
		$limit = 20;
		$distance = 5000;

		$newTable = new \App\Table\RWGPS();
		$oldTable = new \Tests\Table\RWGPS();

		$this->setToMySQL();
		$expected = $oldTable->closest($lat, $long, $limit, $distance);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->closest($lat, $long, $limit, $distance);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals(\count($expected), \count($actual));
		}

	public function testDistanceFrom() : void
		{
		// parameters: float $latitude, float $longitude
		// test type: static
		// variables: $latitude, $longitude

		$latitude = 40.9960226;
		$longitude = -73.806616;

		$newTable = new \App\Table\RWGPS();
		$oldTable = new \Tests\Table\RWGPS();

		$this->setToMySQL();
		$expected = $oldTable->distanceFrom($latitude, $longitude);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->distanceFrom($latitude, $longitude);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected->getRecordCursor(), $actual->getRecordCursor(), __METHOD__);
		}

	public function testGetOldest() : void
		{
		// parameters: int $limit = 10
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $limit

		$limit = 20;

		$newTable = new \App\Table\RWGPS();
		$oldTable = new \Tests\Table\RWGPS();

		$this->setToMySQL();
		$expected = $oldTable->getOldest($limit);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getOldest($limit);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetUpcomingRWGPS() : void
		{
		// parameters:
		// test type: \PHPFUI\ORM\RecordCursor
		// variables:

		$newTable = new \App\Table\RWGPS();
		$oldTable = new \Tests\Table\RWGPS();

		$this->setToMySQL();
		$expected = $oldTable->getUpcomingRWGPS();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getUpcomingRWGPS();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__, 'RWGPSId');
		}

	public function testSetNonClubBetween() : void
		{
		// parameters: string $startDate = '', string $endDate = ''
		// test type: static
		// variables: $startDate, $endDate

		$startDate = '2025-01-01';
		$endDate = '2026-01-01';

		$newTable = new \App\Table\RWGPS();
		$oldTable = new \Tests\Table\RWGPS();

		$this->setToMySQL();
		$expected = $oldTable->setNonClubBetween($startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->setNonClubBetween($startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected->getRecordCursor(), $actual->getRecordCursor(), __METHOD__);
		}
	}
