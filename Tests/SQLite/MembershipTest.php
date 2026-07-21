<?php

namespace Tests\SQLite;

class MembershipTest extends \Tests\SQLAsserts
	{
	public function testBadExpirations() : void
		{
		// parameters:
		// test type: static
		// variables:


		$newTable = new \App\Table\Membership();
		$oldTable = new \Tests\Table\Membership();

		$this->setToMySQL();
		$expected = $oldTable->badExpirations();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->badExpirations();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->getRecordCursor(), $actual->getRecordCursor(), __METHOD__);
		}

	public function testCurrentMembershipCount() : void
		{
		// parameters:
		// test type: ?string
		// variables:


		$newTable = new \App\Table\Membership();
		$oldTable = new \Tests\Table\Membership();

		$this->setToMySQL();
		$expected = $oldTable->currentMembershipCount();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->currentMembershipCount();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testCurrentSubscriptionCount() : void
		{
		// parameters:
		// test type: int
		// variables:


		$newTable = new \App\Table\Membership();
		$oldTable = new \Tests\Table\Membership();

		$this->setToMySQL();
		$expected = $oldTable->currentSubscriptionCount();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->currentSubscriptionCount();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testGetExpiringMemberships() : void
		{
		// parameters: string $start, string $end
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $start, $end

		$start = '2026-01-01';
		$end = '2027-01-01';

		$newTable = new \App\Table\Membership();
		$oldTable = new \Tests\Table\Membership();

		$this->setToMySQL();
		$expected = $oldTable->getExpiringMemberships($start, $end);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getExpiringMemberships($start, $end);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetMembershipsLastNames() : void
		{
		// parameters: int $membershipId
		// test type: string
		// variables: $membershipId

		$membershipId = 2989;

		$newTable = new \App\Table\Membership();
		$oldTable = new \Tests\Table\Membership();

		$this->setToMySQL();
		$expected = $oldTable->getMembershipsLastNames($membershipId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getMembershipsLastNames($membershipId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testGetOldestMembership() : void
		{
		// parameters:
		// test type: \App\Record\Membership
		// variables:


		$newTable = new \App\Table\Membership();
		$oldTable = new \Tests\Table\Membership();

		$this->setToMySQL();
		$expected = $oldTable->getOldestMembership();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getOldestMembership();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->toArray(), $actual->toArray(), __METHOD__);
		}

	public function testGetRenewedMemberships() : void
		{
		// parameters: int $daysBack
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $daysBack

		$daysBack = 30;

		$newTable = new \App\Table\Membership();
		$oldTable = new \Tests\Table\Membership();

		$this->setToMySQL();
		$expected = $oldTable->getRenewedMemberships($daysBack);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getRenewedMemberships($daysBack);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetRenewingMemberships() : void
		{
		// parameters: string $date
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $date

		$date = '2026-06-01';

		$newTable = new \App\Table\Membership();
		$oldTable = new \Tests\Table\Membership();

		$this->setToMySQL();
		$expected = $oldTable->getRenewingMemberships($date);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getRenewingMemberships($date);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testNoMembers() : void
		{
		// parameters:
		// test type: static
		// variables:


		$newTable = new \App\Table\Membership();
		$oldTable = new \Tests\Table\Membership();

		$this->setToMySQL();
		$expected = $oldTable->noMembers();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->noMembers();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->getRecordCursor(), $actual->getRecordCursor(), __METHOD__);
		}

	public function testNoPayments() : void
		{
		// parameters:
		// test type: static
		// variables:


		$newTable = new \App\Table\Membership();
		$oldTable = new \Tests\Table\Membership();

		$this->setToMySQL();
		$expected = $oldTable->noPayments();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->noPayments();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->getRecordCursor(), $actual->getRecordCursor(), __METHOD__);
		}
	}
