<?php

namespace Tests\SQLite;

class JobShiftTest extends \Tests\SQLAsserts
	{
	public function testGetAvailableJobShifts() : void
		{
		// parameters: int $jobId
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $jobId

		$jobId = 413;

		$newTable = new \App\Table\JobShift();
		$oldTable = new \Tests\Table\JobShift();

		$this->setToMySQL();
		$expected = $oldTable->getAvailableJobShifts($jobId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getAvailableJobShifts($jobId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetJobShifts() : void
		{
		// parameters: int $jobId
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $jobId

		$jobId = 413;

		$newTable = new \App\Table\JobShift();
		$oldTable = new \Tests\Table\JobShift();

		$this->setToMySQL();
		$expected = $oldTable->getJobShifts($jobId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getJobShifts($jobId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}
	}
