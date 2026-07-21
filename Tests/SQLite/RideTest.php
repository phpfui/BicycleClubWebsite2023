<?php

namespace Tests\SQLite;

class RideTest extends \Tests\SQLAsserts
	{
	public function testDistanceToRide() : void
		{
		// parameters: float $latitude, float $longitude, string $startDate, string $endDate
		// test type: static
		// variables: $latitude, $longitude, $startDate, $endDate

		$latitude = 40.9960226;
		$longitude = -73.806616;
		$startDate = '2025-01-01';
		$endDate = '2026-01-01';

		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->distanceToRide($latitude, $longitude, $startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->distanceToRide($latitude, $longitude, $startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected->getDataObjectCursor(), $actual->getDataObjectCursor(), __METHOD__, 'rideId');
		}

	public function testFind() : void
		{
		// parameters: array $parameters
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $parameters

		$parameters = [
			'start' => '2026-06-08',
			'end' => '2026-07-08',
			'maxDistance' => '24',
			'title' => 'fling',
		];

		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->find($parameters);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->find($parameters);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testFutureRidesForMember() : void
		{
		// parameters: \App\Record\Member $member
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $member

		$member = new \App\Record\Member(2590);

		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->futureRidesForMember($member);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->futureRidesForMember($member);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetCountByStartLocation() : void
		{
		// parameters:
		// test type: array
		// variables:


		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->getCountByStartLocation();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getCountByStartLocation();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testGetCueSheetRideCount() : void
		{
		// parameters: \App\Record\CueSheet $cueSheet
		// test type: int
		// variables: $cueSheet

		$cueSheet = new \App\Record\CueSheet(5);

		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->getCueSheetRideCount($cueSheet);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getCueSheetRideCount($cueSheet);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testGetCuesheetStats() : void
		{
		// parameters: int $year
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $year

		$year = 2015;

		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->getCuesheetStats($year);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getCuesheetStats($year);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__, 'cueSheetId');
		}

	public function testGetDateRange() : void
		{
		// parameters: int $start, int $end
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $start, $end

		$start = '2025-01-01';
		$end = '2026-01-01';

		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->getDateRange(\App\Tools\Date::fromString($start), \App\Tools\Date::fromString($end));
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getDateRange(\App\Tools\Date::fromString($start), \App\Tools\Date::fromString($end));
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetFirstRideWithCueSheet() : void
		{
		// parameters:
		// test type: \App\Record\Ride
		// variables:


		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->getFirstRideWithCueSheet();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getFirstRideWithCueSheet();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->toArray(), $actual->toArray(), __METHOD__);
		}

	public function testGetForMemberDate() : void
		{
		// parameters: int $memberId, string $startDate, string $endDate
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $memberId, $startDate, $endDate

		$memberId = 2590;
		$startDate = '2024-01-01';
		$endDate = '2026-01-01';

		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->getForMemberDate($memberId, $startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getForMemberDate($memberId, $startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetLatestRideWithCueSheet() : void
		{
		// parameters:
		// test type: \App\Record\Ride
		// variables:


		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->getLatestRideWithCueSheet();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getLatestRideWithCueSheet();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->toArray(), $actual->toArray(), __METHOD__);
		}

	public function testGetLeadersRides() : void
		{
		// parameters: array $categories, string $startDate, string $endDate, array $leaderTypes = [0]
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $categories, $startDate, $endDate, $leaderTypes

		$categories = [5];
		$startDate = '2026-01-01';
		$endDate = '2026-06-01';
		$leaderTypes = [0];

		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->getLeadersRides($categories, $startDate, $endDate, $leaderTypes);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getLeadersRides($categories, $startDate, $endDate, $leaderTypes);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetMyCategoryRides() : void
		{
		// parameters: \App\Record\Member $member
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $member

		$member = new \App\Record\Member(2590);

		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->getMyCategoryRides($member);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getMyCategoryRides($member);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetMyDateRange() : void
		{
		// parameters: string $start, string $end, \App\Enum\RideSignup\Attended $status
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $start, $end, $status

		$start = '2026-01-01';
		$end = '2026-06-01';
		$status = \App\Enum\RideSignup\Attended::CONFIRMED;

		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->getMyDateRange($start, $end, $status);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getMyDateRange($start, $end, $status);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetMyNewest() : void
		{
		// parameters:
		// test type: \App\Record\Ride
		// variables:


		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->getMyNewest();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getMyNewest();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->toArray(), $actual->toArray(), __METHOD__);
		}

	public function testGetMyOldest() : void
		{
		// parameters:
		// test type: \App\Record\Ride
		// variables:


		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->getMyOldest();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getMyOldest();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->toArray(), $actual->toArray(), __METHOD__);
		}

	public function testGetMyPendingRides() : void
		{
		// parameters: \App\Record\Member $member
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $member

		$member = new \App\Record\Member(2590);

		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->getMyPendingRides($member);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getMyPendingRides($member);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetNewest() : void
		{
		// parameters:
		// test type: \App\Record\Ride
		// variables:


		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->getNewest();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getNewest();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->toArray(), $actual->toArray(), __METHOD__);
		}

	public function testGetNewlyAddedUpcomingRides() : void
		{
		// parameters: string $start, string $end = '', int $pending = 0
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $start, $end, $pending

		$start = '2026-07-01';
		$end = '2026-07-09';
		$pending = 0;

		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->getNewlyAddedUpcomingRides($start, $end, $pending);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getNewlyAddedUpcomingRides($start, $end, $pending);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetOldest() : void
		{
		// parameters:
		// test type: \App\Record\Ride
		// variables:


		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->getOldest();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getOldest();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->toArray(), $actual->toArray(), __METHOD__);
		}

	public function testGetRidesForLocation() : void
		{
		// parameters: int $startLocationId, string $date = ''
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $startLocationId, $date

		$startLocationId = 65;
		$date = '2026--0-17';

		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->getRidesForLocation($startLocationId, $date);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getRidesForLocation($startLocationId, $date);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetRideStatus() : void
		{
		// parameters: string $startDate, string $endDate
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $startDate, $endDate

		$startDate = '2024-01-01';
		$endDate = '2026-01-01';

		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->getRideStatus($startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getRideStatus($startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetRideStatusUnawarded() : void
		{
		// parameters: string $startDate, string $endDate
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $startDate, $endDate

		$startDate = '2024-01-01';
		$endDate = '2026-01-01';

		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->getRideStatusUnawarded($startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getRideStatusUnawarded($startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetRWGPSStats() : void
		{
		// parameters: \App\Record\RWGPS $rwgps
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $rwgps

		$rwgps = new \App\Record\RWGPS(32967711);

		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->getRWGPSStats($rwgps);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getRWGPSStats($rwgps);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetStatusValues() : void
		{
		// parameters:
		// test type: array
		// variables:


		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->getStatusValues();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getStatusValues();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testLatestRideForAssistant() : void
		{
		// parameters: int $memberId
		// test type: \App\Record\Ride
		// variables: $memberId

		$memberId = 2590;

		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->latestRideForAssistant($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->latestRideForAssistant($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->toArray(), $actual->toArray(), __METHOD__);
		}

	public function testLatestRideForMember() : void
		{
		// parameters: int $memberId
		// test type: \App\Record\Ride
		// variables: $memberId

		$memberId = 2590;

		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->latestRideForMember($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->latestRideForMember($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->toArray(), $actual->toArray(), __METHOD__);
		}

	public function testOldestRideForAssistant() : void
		{
		// parameters: int $memberId
		// test type: \App\Record\Ride
		// variables: $memberId

		$memberId = 2590;

		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->oldestRideForAssistant($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->oldestRideForAssistant($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->toArray(), $actual->toArray(), __METHOD__);
		}

	public function testOldestRideForMember() : void
		{
		// parameters: int $memberId
		// test type: \App\Record\Ride
		// variables: $memberId

		$memberId = 2590;

		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->oldestRideForMember($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->oldestRideForMember($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->toArray(), $actual->toArray(), __METHOD__);
		}

	public function testPastRidesForAssistant() : void
		{
		// parameters: \App\Record\Member $member, int $limit = 50, int $year = 0
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $member, $limit, $year

		$member = new \App\Record\Member(2590);
		$limit = 20;
		$year = 2025;

		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->pastRidesForAssistant($member, $limit, $year);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->pastRidesForAssistant($member, $limit, $year);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testPastRidesForMember() : void
		{
		// parameters: \App\Record\Member $member, int $limit = 50, int $year = 0
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $member, $limit, $year

		$member = new \App\Record\Member(2590);
		$limit = 30;
		$year = 2024;

		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->pastRidesForMember($member, $limit, $year);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->pastRidesForMember($member, $limit, $year);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testSetInventoryBySignup() : void
		{
		// parameters: string $rideDate
		// test type: static
		// variables: $rideDate

		$rideDate = '2026-06-17';

		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->setInventoryBySignup($rideDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->setInventoryBySignup($rideDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected->getDataObjectCursor(), $actual->getDataObjectCursor(), __METHOD__);
		}

	public function testSetRidesForCueSheetCursor() : void
		{
		// parameters: \App\Record\CueSheet $cuesheet
		// test type: static
		// variables: $cuesheet

		$cuesheet = new \App\Record\CueSheet(5);

		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->setRidesForCueSheetCursor($cuesheet);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->setRidesForCueSheetCursor($cuesheet);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected->getDataObjectCursor(), $actual->getDataObjectCursor(), __METHOD__);
		}

	public function testSetRidesForLocationCursor() : void
		{
		// parameters: \App\Record\StartLocation $startLocation, string $date = ''
		// test type: static
		// variables: $startLocation, $date

		$startLocation = new \App\Record\StartLocation(65);
		$date = '2026-06-17';

		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->setRidesForLocationCursor($startLocation, $date);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->setRidesForLocationCursor($startLocation, $date);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected->getDataObjectCursor(), $actual->getDataObjectCursor(), __METHOD__);
		}

	public function testUnreportedRides() : void
		{
		// parameters:
		// test type: \PHPFUI\ORM\RecordCursor
		// variables:


		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->unreportedRides();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->unreportedRides();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testUnreportedRidesForMember() : void
		{
		// parameters: int $memberId
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $memberId

		$memberId = 2590;

		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->unreportedRidesForMember($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->unreportedRidesForMember($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testUnreportedRidesOn() : void
		{
		// parameters: array $dates
		// test type: ?\PHPFUI\ORM\RecordCursor
		// variables: $dates

		$dates = [];

		$start = \App\Tools\Date::fromString('2026-01-01');
		$end = \App\Tools\Date::fromString('2026-07-01');

		for ($i = $start; $i < $end; ++$i)
			{
			$dates[] = \App\Tools\Date::toString($i);
			}

		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->unreportedRidesOn($dates);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->unreportedRidesOn($dates);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testUpcomingRides() : void
		{
		// parameters: int $limit = 0
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $limit

		$limit = 50;

		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->upcomingRides($limit);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->upcomingRides($limit);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testWith() : void
		{
		// parameters: \App\Record\Member $with
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $with

		$with = new \App\Record\Member(2590);

		$newTable = new \App\Table\Ride();
		$oldTable = new \Tests\Table\Ride();

		$this->setToMySQL();
		$expected = $oldTable->with($with);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->with($with);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}
	}
