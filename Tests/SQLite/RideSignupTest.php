<?php

namespace Tests\SQLite;

class RideSignupTest extends \Tests\SQLAsserts
	{
	public function testFind() : void
		{
		// parameters: array $parameters
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $parameters

		$parameters = [];

		$newTable = new \App\Table\RideSignup();
		$oldTable = new \Tests\Table\RideSignup();

		$this->setToMySQL();
		$expected = $oldTable->find($parameters);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->find($parameters);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__, 'rideId');
		}

	public function testGetAllSignedUpRiders() : void
		{
		// parameters: \App\Record\Ride $ride, bool $sortByStatus = true
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $ride, $sortByStatus

		$ride = new \App\Record\Ride(26243);
		$sortByStatus = true;

		$newTable = new \App\Table\RideSignup();
		$oldTable = new \Tests\Table\RideSignup();

		$this->setToMySQL();
		$expected = $oldTable->getAllSignedUpRiders($ride, $sortByStatus);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getAllSignedUpRiders($ride, $sortByStatus);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__, 'memberId');
		}

	public function testGetAttendedStatus() : void
		{
		// parameters:
		// test type: array
		// variables:


		$newTable = new \App\Table\RideSignup();
		$oldTable = new \Tests\Table\RideSignup();

		$this->setToMySQL();
		$expected = $oldTable->getAttendedStatus();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getAttendedStatus();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testGetCommittedRiders() : void
		{
		// parameters: \App\Record\Ride $ride
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $ride

		$ride = new \App\Record\Ride(26243);

		$newTable = new \App\Table\RideSignup();
		$oldTable = new \Tests\Table\RideSignup();

		$this->setToMySQL();
		$expected = $oldTable->getCommittedRiders($ride);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getCommittedRiders($ride);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetEarliestRiderSignupTime() : void
		{
		// parameters: \App\Record\Member $rider, string $date
		// test type: string
		// variables: $rider, $date

		$rider = new \App\Record\Member(2590);
		$date = '2026-06-14';

		$newTable = new \App\Table\RideSignup();
		$oldTable = new \Tests\Table\RideSignup();

		$this->setToMySQL();
		$expected = $oldTable->getEarliestRiderSignupTime($rider, $date);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getEarliestRiderSignupTime($rider, $date);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testGetMemberRidesForDate() : void
		{
		// parameters: \App\Record\Member $member, string $date
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $member, $date

		$member = new \App\Record\Member(2590);
		$date = '2026-06-14';

		$newTable = new \App\Table\RideSignup();
		$oldTable = new \Tests\Table\RideSignup();

		$this->setToMySQL();
		$expected = $oldTable->getMemberRidesForDate($member, $date);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getMemberRidesForDate($member, $date);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetNewest() : void
		{
		// parameters: \App\Record\Member $member
		// test type: array
		// variables: $member

		$member = new \App\Record\Member(2590);

		$newTable = new \App\Table\RideSignup();
		$oldTable = new \Tests\Table\RideSignup();

		$this->setToMySQL();
		$expected = $oldTable->getNewest($member);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getNewest($member);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testGetOldest() : void
		{
		// parameters: \App\Record\Member $member
		// test type: array
		// variables: $member

		$member = new \App\Record\Member(2590);

		$newTable = new \App\Table\RideSignup();
		$oldTable = new \Tests\Table\RideSignup();

		$this->setToMySQL();
		$expected = $oldTable->getOldest($member);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getOldest($member);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testGetRidersForAttended() : void
		{
		// parameters: \App\Record\Ride $ride, \App\Enum\RideSignup\Attended $attended = \App\Enum\RideSignup\Attended::CONFIRMED
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $ride, $attended

		$ride = new \App\Record\Ride(25111);
		$attended = \App\Enum\RideSignup\Attended::CONFIRMED;

		$newTable = new \App\Table\RideSignup();
		$oldTable = new \Tests\Table\RideSignup();

		$this->setToMySQL();
		$expected = $oldTable->getRidersForAttended($ride, $attended);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getRidersForAttended($ride, $attended);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetRidersForStatus() : void
		{
		// parameters: \App\Record\Ride $ride, \App\Enum\RideSignup\Status $status
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $ride, $status

		$ride = new \App\Record\Ride(25111);
		$status = \App\Enum\RideSignup\Status::DEFINITELY_RIDING;

		$newTable = new \App\Table\RideSignup();
		$oldTable = new \Tests\Table\RideSignup();

		$this->setToMySQL();
		$expected = $oldTable->getRidersForStatus($ride, $status);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getRidersForStatus($ride, $status);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetRiderStatus() : void
		{
		// parameters:
		// test type: array
		// variables:


		$newTable = new \App\Table\RideSignup();
		$oldTable = new \Tests\Table\RideSignup();

		$this->setToMySQL();
		$expected = $oldTable->getRiderStatus();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getRiderStatus();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testGetRidesForMember() : void
		{
		// parameters: \App\Record\Member $member, string $startDate = '2000-01-01', string $endDate = '2999-12-31'
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $member, $startDate, $endDate

		$member = new \App\Record\Member(2590);
		$startDate = '2025-01-01';
		$endDate = '2026-01-01';

		$newTable = new \App\Table\RideSignup();
		$oldTable = new \Tests\Table\RideSignup();

		$this->setToMySQL();
		$expected = $oldTable->getRidesForMember($member, $startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getRidesForMember($member, $startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__, 'rideId');
		}

	public function testGetSignedUpByPermmission() : void
		{
		// parameters: \App\Record\Ride $ride, \App\Record\Permission $permission
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $ride, $permission

		$ride = new \App\Record\Ride(25111);
		$permission = new \App\Table\Setting()->getStandardPermissionGroup('Ride Leader');

		$newTable = new \App\Table\RideSignup();
		$oldTable = new \Tests\Table\RideSignup();

		$this->setToMySQL();
		$expected = $oldTable->getSignedUpByPermmission($ride, $permission);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getSignedUpByPermmission($ride, $permission);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetSignedUpRiders() : void
		{
		// parameters: int $rideId, string $order = 'r.signedUpTime'
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $rideId, $order

		$rideId = 2590;
		$order = 'signedUpTime';

		$newTable = new \App\Table\RideSignup();
		$oldTable = new \Tests\Table\RideSignup();

		$this->setToMySQL();
		$expected = $oldTable->getSignedUpRiders($rideId, $order);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getSignedUpRiders($rideId, $order);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}
	}
