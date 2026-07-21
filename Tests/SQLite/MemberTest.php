<?php

namespace Tests\SQLite;

class MemberTest extends \Tests\SQLAsserts
	{
	public function testAbandoned() : void
		{
		// parameters:
		// test type: static
		// variables:


		$newTable = new \App\Table\Member();
		$oldTable = new \Tests\Table\Member();

		$this->setToMySQL();
		$expected = $oldTable->abandoned();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->abandoned();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected->getDataObjectCursor(), $actual->getDataObjectCursor(), __METHOD__);
		}

	public function testCurrentMemberCount() : void
		{
		// parameters:
		// test type: int
		// variables:


		$newTable = new \App\Table\Member();
		$oldTable = new \Tests\Table\Member();

		$this->setToMySQL();
		$expected = $oldTable->currentMemberCount();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->currentMemberCount();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testFind() : void
		{
		// parameters: array $parameters
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $parameters

		$parameters = ['lastName' => 'wells', 'all' => true, 'categories' => [5]];

		$newTable = new \App\Table\Member();
		$oldTable = new \Tests\Table\Member();

		$this->setToMySQL();
		$expected = $oldTable->find($parameters);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->find($parameters);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testFindByName() : void
		{
		// parameters: array $names, bool $currentMembers = true
		// test type: \PHPFUI\ORM\ArrayCursor
		// variables: $names, $currentMembers

		$names = ['wells'];
		$currentMembers = true;

		$newTable = new \App\Table\Member();
		$oldTable = new \Tests\Table\Member();

		$this->setToMySQL();
		$expected = $oldTable->findByName($names, $currentMembers);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->findByName($names, $currentMembers);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertArrayCursorEquals($expected, $actual, __METHOD__, 'email');
		}

	public function testGetAllMembers() : void
		{
		// parameters: string $expirationStart = '', string $expirationEnd = ''
		// test type: \PHPFUI\ORM\ArrayCursor
		// variables: $expirationStart, $expirationEnd

		$expirationStart = '2025-01-01';
		$expirationEnd = '2025-03-01';

		$newTable = new \App\Table\Member();
		$oldTable = new \Tests\Table\Member();

		$this->setToMySQL();
		$expected = $oldTable->getAllMembers($expirationStart, $expirationEnd);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getAllMembers($expirationStart, $expirationEnd);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertArrayCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetEmailableMembers() : void
		{
		// parameters: bool $all, bool $current, int $monthsPast = 0, int $monthsNew = 0, array $categories = [], string $extra = ''
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $all, $current, $monthsPast, $monthsNew, $categories, $extra

		$all = true;
		$current = true;
		$monthsPast = 5;
		$monthsNew = 5;
		$categories = [5];
		$extra = '';

		$newTable = new \App\Table\Member();
		$oldTable = new \Tests\Table\Member();

		$this->setToMySQL();
		$expected = $oldTable->getEmailableMembers($all, $current, $monthsPast, $monthsNew, $categories, $extra);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getEmailableMembers($all, $current, $monthsPast, $monthsNew, $categories, $extra);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual->getRecordCursor(), __METHOD__);
		}

	public function testGetJournalMembers() : void
		{
		// parameters: string $expires
		// test type: \PHPFUI\ORM\ArrayCursor
		// variables: $expires

		$expires = '2026-06-01';

		$newTable = new \App\Table\Member();
		$oldTable = new \Tests\Table\Member();

		$this->setToMySQL();
		$expected = $oldTable->getJournalMembers($expires);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getJournalMembers($expires);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertArrayCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetJournalRideInterests() : void
		{
		// parameters:
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables:

		$newTable = new \App\Table\Member();
		$oldTable = new \Tests\Table\Member();

		$this->setToMySQL();
		$expected = $oldTable->getJournalRideInterests();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getJournalRideInterests();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

// sqlite returns 0 for count here, but works on the website, skipping
//	public function testGetLeaders() : void
//		{
//		// parameters: array $categories = [], string $type = 'Ride Leader', ?string $fromDate = null, ?string $toDate = null, ?string $minLed = null, ?string $maxLed = null
//		// test type: \PHPFUI\ORM\RecordCursor
//		// variables: $categories, $type, $fromDate, $toDate, $minLed, $maxLed
//
//		$categories = [5];
//		$type = 'Ride Leader';
//		$fromDate = '2025-01-01';
//		$toDate = '2026-01-01';
//		$minLed = 1;
//		$maxLed = 5;
//
//		$newTable = new \App\Table\Member();
//		$oldTable = new \Tests\Table\Member();
//
//		$this->setToMySQL();
//		$expected = $oldTable->getLeaders($categories, $type, $fromDate, $toDate, $minLed, $maxLed);
//		$this->assertNoSQLErrors(__METHOD__);
//
//		$this->setToSQLite();
//		$actual = $newTable->getLeaders($categories, $type, $fromDate, $toDate, $minLed, $maxLed);
//		$this->assertNoSQLErrors(__METHOD__);
//
//		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__, 'memberId');
//		}

	public function testGetMembership() : void
		{
		// parameters: int $memberId
		// test type: array
		// variables: $memberId

		$memberId = 2590;

		$newTable = new \App\Table\Member();
		$oldTable = new \Tests\Table\Member();

		$this->setToMySQL();
		$expected = $oldTable->getMembership($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getMembership($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testGetMembershipCursor() : void
		{
		// parameters: int $memberId
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $memberId

		$memberId = 2590;

		$newTable = new \App\Table\Member();
		$oldTable = new \Tests\Table\Member();

		$this->setToMySQL();
		$expected = $oldTable->getMembershipCursor($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getMembershipCursor($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetMembershipObject() : void
		{
		// parameters: int $memberId
		// test type: \PHPFUI\ORM\DataObject
		// variables: $memberId

		$memberId = 2590;

		$newTable = new \App\Table\Member();
		$oldTable = new \Tests\Table\Member();

		$this->setToMySQL();
		$expected = $oldTable->getMembershipObject($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getMembershipObject($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->toArray(), $actual->toArray(), __METHOD__);
		}

	public function testGetMembersWithPermission() : void
		{
		// parameters: string $permissionName
		// test type: static
		// variables: $permissionName

		$permissionName = 'Normal Member';

		$newTable = new \App\Table\Member();
		$oldTable = new \Tests\Table\Member();

		$this->setToMySQL();
		$expected = $oldTable->getMembersWithPermission($permissionName);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getMembersWithPermission($permissionName);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected->getDataObjectCursor(), $actual->getDataObjectCursor(), __METHOD__);
		}

	public function testGetMembersWithPermissionId() : void
		{
		// parameters: int $permissionId
		// test type: static
		// variables: $permissionId

		$permissionId = 2;

		$newTable = new \App\Table\Member();
		$oldTable = new \Tests\Table\Member();

		$this->setToMySQL();
		$expected = $oldTable->getMembersWithPermissionId($permissionId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getMembersWithPermissionId($permissionId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected->getDataObjectCursor(), $actual->getDataObjectCursor(), __METHOD__);
		}

	public function testGetName() : void
		{
		// parameters: ?int $memberId
		// test type: string
		// variables: $memberId

		$memberId = 2590;

		$newTable = new \App\Table\Member();
		$oldTable = new \Tests\Table\Member();

		$this->setToMySQL();
		$expected = $oldTable->getName($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getName($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

// this test produces a MySQL error for no reason, duplicate membershipId field
//	public function testGetNewMembers() : void
//		{
//		// parameters: string $start, string $end
//		// test type: \PHPFUI\ORM\DataObjectCursor
//		// variables: $start, $end
//
//		$start = '2026-05-01';
//		$end = '2026-06-01';
//
//		$newTable = new \App\Table\Member();
//		$oldTable = new \Tests\Table\Member();
//
//		$this->setToMySQL();
//		$expected = $oldTable->getNewMembers($start, $end);
//		$this->assertNoSQLErrors(__METHOD__);
//
//		$this->setToSQLite();
//		$actual = $newTable->getNewMembers($start, $end);
//		$this->assertNoSQLErrors(__METHOD__);
//
//		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__, 'memberId');
//		}

	public function testGetNewRideInterests() : void
		{
		// parameters: int $categoryId
		// test type: \PHPFUI\ORM\ArrayCursor
		// variables: $categoryId

		$categoryId = 5;

		$newTable = new \App\Table\Member();
		$oldTable = new \Tests\Table\Member();

		$this->setToMySQL();
		$expected = $oldTable->getNewRideInterests($categoryId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getNewRideInterests($categoryId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertArrayCursorEquals($expected, $actual, __METHOD__, 'email');
		}

	public function testGetNewsletterMembers() : void
		{
		// parameters: string $expires
		// test type: \PHPFUI\ORM\ArrayCursor
		// variables: $expires

		$expires = '2026-01-01';

		$newTable = new \App\Table\Member();
		$oldTable = new \Tests\Table\Member();

		$this->setToMySQL();
		$expected = $oldTable->getNewsletterMembers($expires);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getNewsletterMembers($expires);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertArrayCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetPendingMembers() : void
		{
		// parameters: string $date
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $date

		$date = '2026-07-04';

		$newTable = new \App\Table\Member();
		$oldTable = new \Tests\Table\Member();

		$this->setToMySQL();
		$expected = $oldTable->getPendingMembers($date);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getPendingMembers($date);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetVolunteersForEvents() : void
		{
		// parameters: array $events
		// test type: \PHPFUI\ORM\ArrayCursor
		// variables: $events

		$events = [409, 408, 407];

		$newTable = new \App\Table\Member();
		$oldTable = new \Tests\Table\Member();

		$this->setToMySQL();
		$expected = $oldTable->getVolunteersForEvents($events);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getVolunteersForEvents($events);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertArrayCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetVolunteersForJob() : void
		{
		// parameters: \App\Record\Job $job
		// test type: \PHPFUI\ORM\ArrayCursor
		// variables: $job

		$job = new \App\Record\Job(417);

		$newTable = new \App\Table\Member();
		$oldTable = new \Tests\Table\Member();

		$this->setToMySQL();
		$expected = $oldTable->getVolunteersForJob($job);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getVolunteersForJob($job);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertArrayCursorEquals($expected, $actual, __METHOD__);
		}

	public function testLastSignIns() : void
		{
		// parameters: int $days
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $days

		$days = 30;

		$newTable = new \App\Table\Member();
		$oldTable = new \Tests\Table\Member();

		$this->setToMySQL();
		$expected = $oldTable->lastSignIns($days);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->lastSignIns($days);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testMembersInMembership() : void
		{
		// parameters: int $membershipId
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $membershipId

		$membershipId = 2989;

		$newTable = new \App\Table\Member();
		$oldTable = new \Tests\Table\Member();

		$this->setToMySQL();
		$expected = $oldTable->membersInMembership($membershipId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->membersInMembership($membershipId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testMissingNames() : void
		{
		// parameters:
		// test type: static
		// variables:


		$newTable = new \App\Table\Member();
		$oldTable = new \Tests\Table\Member();

		$this->setToMySQL();
		$expected = $oldTable->missingNames();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->missingNames();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected->getDataObjectCursor(), $actual->getDataObjectCursor(), __METHOD__);
		}

	public function testNoPermissions() : void
		{
		// parameters:
		// test type: static
		// variables:


		$newTable = new \App\Table\Member();
		$oldTable = new \Tests\Table\Member();

		$this->setToMySQL();
		$expected = $oldTable->noPermissions();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->noPermissions();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected->getDataObjectCursor(), $actual->getDataObjectCursor(), __METHOD__);
		}

	public function testOutstandingPoints() : void
		{
		// parameters: string $sort
		// test type: \PHPFUI\ORM\ArrayCursor
		// variables: $sort

		$sort = 'memberId';

		$newTable = new \App\Table\Member();
		$oldTable = new \Tests\Table\Member();

		$this->setToMySQL();
		$expected = $oldTable->outstandingPoints($sort);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->outstandingPoints($sort);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertArrayCursorEquals($expected, $actual, __METHOD__);
		}
	}
