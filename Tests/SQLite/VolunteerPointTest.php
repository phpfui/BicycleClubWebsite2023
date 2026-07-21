<?php

namespace Tests\SQLite;

class VolunteerPointTest extends \Tests\SQLAsserts
	{
	public function testGetForMemberDate() : void
		{
		// parameters: int $memberId, string $startDate, string $endDate
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $memberId, $startDate, $endDate

		$memberId = 2590;
		$startDate = '2025-01-01';
		$endDate = '2025-12-31';

		$newTable = new \App\Table\VolunteerPoint();
		$oldTable = new \Tests\Table\VolunteerPoint();

		$this->setToMySQL();
		$expected = $oldTable->getForMemberDate($memberId, $startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getForMemberDate($memberId, $startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}
	}
