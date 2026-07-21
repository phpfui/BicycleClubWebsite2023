<?php

namespace Tests\SQLite;

class AssistantLeaderTest extends \Tests\SQLAsserts
	{
	public function testGetForDateRange() : void
		{
		// parameters: string $startDate, string $endDate
		// test type: \PHPFUI\ORM\ArrayCursor
		// variables: $startDate, $endDate

		$startDate = '2025-01-01';
		$endDate = '2025-12-31';

		$newTable = new \App\Table\AssistantLeader();
		$oldTable = new \Tests\Table\AssistantLeader();

		$this->setToMySQL();
		$expected = $oldTable->getForDateRange($startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getForDateRange($startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertArrayCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetForMemberDate() : void
		{
		// parameters: int $memberId, string $startDate, string $endDate
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $memberId, $startDate, $endDate

		$memberId = 2590;
		$startDate = '2025-01-01';
		$endDate = '2025-12-31';

		$newTable = new \App\Table\AssistantLeader();
		$oldTable = new \Tests\Table\AssistantLeader();

		$this->setToMySQL();
		$expected = $oldTable->getForMemberDate($memberId, $startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getForMemberDate($memberId, $startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetForRide() : void
		{
		// parameters: \App\Record\Ride $ride
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $ride

		$ride = new \App\Record\Ride(2500);

		$newTable = new \App\Table\AssistantLeader();
		$oldTable = new \Tests\Table\AssistantLeader();

		$this->setToMySQL();
		$expected = $oldTable->getForRide($ride);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getForRide($ride);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}
	}
