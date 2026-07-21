<?php

namespace Tests\SQLite;

class EventTest extends \Tests\SQLAsserts
	{
	public function testGetAvailableForMember() : void
		{
		// parameters: \App\Record\Member $member
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $member

		$member = new \App\Record\Member(2590);

		$newTable = new \App\Table\Event();
		$oldTable = new \Tests\Table\Event();

		$this->setToMySQL();
		$expected = $oldTable->getAvailableForMember($member);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getAvailableForMember($member);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetFirst() : void
		{
		// parameters: int $memberId = 0
		// test type: string
		// variables: $memberId

		$memberId = 2590;

		$newTable = new \App\Table\Event();
		$oldTable = new \Tests\Table\Event();

		$this->setToMySQL();
		$expected = $oldTable->getFirst($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getFirst($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testGetLast() : void
		{
		// parameters: int $memberId = 0
		// test type: string
		// variables: $memberId

		$memberId = 2590;

		$newTable = new \App\Table\Event();
		$oldTable = new \Tests\Table\Event();

		$this->setToMySQL();
		$expected = $oldTable->getLast($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getLast($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testGetMostRecentRegistered() : void
		{
		// parameters: int $limit = 10
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $limit

		$limit = 11;

		$newTable = new \App\Table\Event();
		$oldTable = new \Tests\Table\Event();

		$this->setToMySQL();
		$expected = $oldTable->getMostRecentRegistered($limit);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getMostRecentRegistered($limit);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetSignedUpForMember() : void
		{
		// parameters: \App\Record\Member $member
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $member

		$member = new \App\Record\Member(2590);

		$newTable = new \App\Table\Event();
		$oldTable = new \Tests\Table\Event();

		$this->setToMySQL();
		$expected = $oldTable->getSignedUpForMember($member);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getSignedUpForMember($member);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testSetEventAttendeeCountCursor() : void
		{
		// parameters:
		// test type: static
		// variables:


		$newTable = new \App\Table\Event();
		$oldTable = new \Tests\Table\Event();

		$this->setToMySQL();
		$expected = $oldTable->setEventAttendeeCountCursor();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->setEventAttendeeCountCursor();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->getDataObjectCursor(), $actual->getDataObjectCursor(), __METHOD__);
		}

	public function testSetUpcomingCursor() : void
		{
		// parameters: bool $publicOnly = true
		// test type: static
		// variables: $publicOnly

		$publicOnly = true;

		$newTable = new \App\Table\Event();
		$oldTable = new \Tests\Table\Event();

		$this->setToMySQL();
		$expected = $oldTable->setUpcomingCursor($publicOnly);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->setUpcomingCursor($publicOnly);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->getRecordCursor(), $actual->getRecordCursor(), __METHOD__);
		}
	}
