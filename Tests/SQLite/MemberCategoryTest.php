<?php

namespace Tests\SQLite;

class MemberCategoryTest extends \Tests\SQLAsserts
	{
	public function testGetRideCategoriesForMember() : void
		{
		// parameters: ?int $memberId
		// test type: array
		// variables: $memberId

		$memberId = 2590;

		$newTable = new \App\Table\MemberCategory();
		$oldTable = new \Tests\Table\MemberCategory();

		$this->setToMySQL();
		$expected = $oldTable->getRideCategoriesForMember($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getRideCategoriesForMember($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testGetRideCategoryStringForMember() : void
		{
		// parameters: int $memberId
		// test type: string
		// variables: $memberId

		$memberId = 2590;

		$newTable = new \App\Table\MemberCategory();
		$oldTable = new \Tests\Table\MemberCategory();

		$this->setToMySQL();
		$expected = $oldTable->getRideCategoryStringForMember($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getRideCategoryStringForMember($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}
	}
