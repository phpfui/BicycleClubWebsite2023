<?php

namespace Tests\SQLite;

class SigninSheetTest extends \Tests\SQLAsserts
	{
	public function testGetForDateRange() : void
		{
		// parameters: string $startDate, string $endDate
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $startDate, $endDate

		$startDate = '2010-01-01';
		$endDate = '2025-01-01';

		$newTable = new \App\Table\SigninSheet();
		$oldTable = new \Tests\Table\SigninSheet();

		$this->setToMySQL();
		$expected = $oldTable->getForDateRange($startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getForDateRange($startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetForMemberDate() : void
		{
		// parameters: int $memberId, string $startDate, string $endDate
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $memberId, $startDate, $endDate

		$memberId = 5154;
		$startDate = '2010-01-01';
		$endDate = '2025-01-01';

		$newTable = new \App\Table\SigninSheet();
		$oldTable = new \Tests\Table\SigninSheet();

		$this->setToMySQL();
		$expected = $oldTable->getForMemberDate($memberId, $startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getForMemberDate($memberId, $startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testSearch() : void
		{
		// parameters: array $parameters
		// test type: bool
		// variables: $parameters

		$parameters = [
			'ride_title' => 'wednesday',
			'rideDateStart' => '2017-07-08',
			'rideDateEnd' => '2020-08-08',
		];

		$newTable = new \App\Table\SigninSheet();
		$oldTable = new \Tests\Table\SigninSheet();

		$this->setToMySQL();
		$expected = $oldTable->search($parameters);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->search($parameters);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}
	}
