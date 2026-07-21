<?php

namespace Tests\SQLite;

class VolunteerPollTest extends \Tests\SQLAsserts
	{
	public function testGetAllPolls() : void
		{
		// parameters:
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables:


		$newTable = new \App\Table\VolunteerPoll();
		$oldTable = new \Tests\Table\VolunteerPoll();

		$this->setToMySQL();
		$expected = $oldTable->getAllPolls();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getAllPolls();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetPolls() : void
		{
		// parameters: \App\Record\JobEvent $jobEvent
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $jobEvent

		$jobEvent = new \App\Record\JobEvent(411);

		$newTable = new \App\Table\VolunteerPoll();
		$oldTable = new \Tests\Table\VolunteerPoll();

		$this->setToMySQL();
		$expected = $oldTable->getPolls($jobEvent);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getPolls($jobEvent);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}
	}
