<?php

namespace Tests\SQLite;

class VolunteerJobShiftTest extends \Tests\SQLAsserts
	{
	public function testGetJobsForEventDateMember() : void
		{
		// parameters: int $jobEventId, string $date, int $memberId
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $jobEventId, $date, $memberId

		$jobEventId = 409;
		$date = '2026-03-06';
		$memberId = 2590;

		$newTable = new \App\Table\VolunteerJobShift();
		$oldTable = new \Tests\Table\VolunteerJobShift();

		$this->setToMySQL();
		$expected = $oldTable->getJobsForEventDateMember($jobEventId, $date, $memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getJobsForEventDateMember($jobEventId, $date, $memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetJobsForMember() : void
		{
		// parameters: int $memberId, int $event = 0
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $memberId, $event

		$memberId = 2590;
		$event = 409;

		$newTable = new \App\Table\VolunteerJobShift();
		$oldTable = new \Tests\Table\VolunteerJobShift();

		$this->setToMySQL();
		$expected = $oldTable->getJobsForMember($memberId, $event);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getJobsForMember($memberId, $event);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetJobVolunteersSince() : void
		{
		// parameters: string $date
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $date

		$date = '2026-03-06';

		$newTable = new \App\Table\VolunteerJobShift();
		$oldTable = new \Tests\Table\VolunteerJobShift();

		$this->setToMySQL();
		$expected = $oldTable->getJobVolunteersSince($date);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getJobVolunteersSince($date);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetShiftsForMember() : void
		{
		// parameters: \App\Record\Job $job, \App\Record\Member $member
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $job, $member

		$job = new \App\Record\Job(711);
		$member = new \App\Record\Member(2590);

		$newTable = new \App\Table\VolunteerJobShift();
		$oldTable = new \Tests\Table\VolunteerJobShift();

		$this->setToMySQL();
		$expected = $oldTable->getShiftsForMember($job, $member);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getShiftsForMember($job, $member);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetUniqueVolunteers() : void
		{
		// parameters: \App\Record\JobEvent $jobEvent
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $jobEvent

		$jobEvent = new \App\Record\JobEvent(409);

		$newTable = new \App\Table\VolunteerJobShift();
		$oldTable = new \Tests\Table\VolunteerJobShift();

		$this->setToMySQL();
		$expected = $oldTable->getUniqueVolunteers($jobEvent);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getUniqueVolunteers($jobEvent);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetVolunteers() : void
		{
		// parameters: int $jobId
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $jobId

		$jobId = 711;

		$newTable = new \App\Table\VolunteerJobShift();
		$oldTable = new \Tests\Table\VolunteerJobShift();

		$this->setToMySQL();
		$expected = $oldTable->getVolunteers($jobId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getVolunteers($jobId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetVolunteersByShift() : void
		{
		// parameters: \App\Record\Job $job
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $job

		$job = new \App\Record\Job(711);

		$newTable = new \App\Table\VolunteerJobShift();
		$oldTable = new \Tests\Table\VolunteerJobShift();

		$this->setToMySQL();
		$expected = $oldTable->getVolunteersByShift($job);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getVolunteersByShift($job);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetVolunteerSchedule() : void
		{
		// parameters: \App\Record\JobEvent $jobEvent
		// test type: \PHPFUI\ORM\ArrayCursor
		// variables: $jobEvent

		$jobEvent = new \App\Record\JobEvent(409);

		$newTable = new \App\Table\VolunteerJobShift();
		$oldTable = new \Tests\Table\VolunteerJobShift();

		$this->setToMySQL();
		$expected = $oldTable->getVolunteerSchedule($jobEvent);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getVolunteerSchedule($jobEvent);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertArrayCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetVolunteersForDates() : void
		{
		// parameters: string $startDate, string $endDate
		// test type: \PHPFUI\ORM\ArrayCursor
		// variables: $startDate, $endDate

		$startDate = '2025-01-01';
		$endDate = '2026-01-01';

		$newTable = new \App\Table\VolunteerJobShift();
		$oldTable = new \Tests\Table\VolunteerJobShift();

		$this->setToMySQL();
		$expected = $oldTable->getVolunteersForDates($startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getVolunteersForDates($startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertArrayCursorEquals($expected, $actual, __METHOD__);
		}

	public function testIsShiftLeader() : void
		{
		// parameters: \App\Record\Job $job, \App\Record\Member $member
		// test type: bool
		// variables: $job, $member

		$job = new \App\Record\Job(678);
		$member = new \App\Record\Member(3275);

		$newTable = new \App\Table\VolunteerJobShift();
		$oldTable = new \Tests\Table\VolunteerJobShift();

		$this->setToMySQL();
		$expected = $oldTable->isShiftLeader($job, $member);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->isShiftLeader($job, $member);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}
	}
