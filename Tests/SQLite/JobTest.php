<?php

namespace Tests\SQLite;

class JobTest extends \Tests\SQLAsserts
	{
	public function testGetJobs() : void
		{
		// parameters: \App\Record\JobEvent $jobEvent
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $jobEvent

		$jobEvent = new \App\Record\JobEvent(411);

		$newTable = new \App\Table\Job();
		$oldTable = new \Tests\Table\Job();

		$this->setToMySQL();
		$expected = $oldTable->getJobs($jobEvent);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getJobs($jobEvent);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}
	}
