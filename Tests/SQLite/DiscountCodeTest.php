<?php

namespace Tests\SQLite;

class DiscountCodeTest extends \Tests\SQLAsserts
	{
	public function testGetActiveCodes() : void
		{
		// parameters:
		// test type: \PHPFUI\ORM\RecordCursor
		// variables:


		$newTable = new \App\Table\DiscountCode();
		$oldTable = new \Tests\Table\DiscountCode();

		$this->setToMySQL();
		$expected = $oldTable->getActiveCodes();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getActiveCodes();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals(\count($expected), \count($actual), __METHOD__);
		}

	public function testGetActiveMembershipCodes() : void
		{
		// parameters: int $membershipType
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $membershipType

		$membershipType = 1;

		$newTable = new \App\Table\DiscountCode();
		$oldTable = new \Tests\Table\DiscountCode();

		$this->setToMySQL();
		$expected = $oldTable->getActiveMembershipCodes($membershipType);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getActiveMembershipCodes($membershipType);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals(\count($expected), \count($actual), __METHOD__);
		}
	}
