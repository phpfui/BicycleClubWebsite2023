<?php

namespace Tests\SQLite;

class MemberOfMonthTest extends \Tests\SQLAsserts
	{
	public function testCurrent() : void
		{
		// parameters:
		// test type: \App\Record\MemberOfMonth
		// variables:


		$newTable = new \App\Table\MemberOfMonth();
		$oldTable = new \Tests\Table\MemberOfMonth();

		$this->setToMySQL();
		$expected = $oldTable->current();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->current();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->toArray(), $actual->toArray(), __METHOD__);
		}

	public function testGetFirst() : void
		{
		// parameters:
		// test type: \App\Record\MemberOfMonth
		// variables:


		$newTable = new \App\Table\MemberOfMonth();
		$oldTable = new \Tests\Table\MemberOfMonth();

		$this->setToMySQL();
		$expected = $oldTable->getFirst();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getFirst();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->toArray(), $actual->toArray(), __METHOD__);
		}

	public function testGetLatest() : void
		{
		// parameters:
		// test type: \App\Record\MemberOfMonth
		// variables:


		$newTable = new \App\Table\MemberOfMonth();
		$oldTable = new \Tests\Table\MemberOfMonth();

		$this->setToMySQL();
		$expected = $oldTable->getLatest();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getLatest();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->toArray(), $actual->toArray(), __METHOD__);
		}

	public function testGetRange() : void
		{
		// parameters: string $first, string $last
		// test type: \PHPFUI\ORM\ArrayCursor
		// variables: $first, $last

		$first = '2017-01-01';
		$last = '2018-01-01';

		$newTable = new \App\Table\MemberOfMonth();
		$oldTable = new \Tests\Table\MemberOfMonth();

		$this->setToMySQL();
		$expected = $oldTable->getRange($first, $last);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getRange($first, $last);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertArrayCursorEquals($expected, $actual, __METHOD__);
		}
	}
