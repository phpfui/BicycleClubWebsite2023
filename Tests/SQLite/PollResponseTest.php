<?php

namespace Tests\SQLite;

class PollResponseTest extends \Tests\SQLAsserts
	{
	public function testGetVotes() : void
		{
		// parameters: int $pollId
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $pollId

		$pollId = 18;

		$newTable = new \App\Table\PollResponse();
		$oldTable = new \Tests\Table\PollResponse();

		$this->setToMySQL();
		$expected = $oldTable->getVotes($pollId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getVotes($pollId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testSetMyMembershipVotesQuery() : void
		{
		// parameters:
		// test type: static
		// variables:


		$newTable = new \App\Table\PollResponse();
		$oldTable = new \Tests\Table\PollResponse();

		$this->setToMySQL();
		$expected = $oldTable->setMyMembershipVotesQuery();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->setMyMembershipVotesQuery();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->getDataObjectCursor(), $actual->getDataObjectCursor(), __METHOD__);
		}

	public function testSetMyVotesQuery() : void
		{
		// parameters:
		// test type: static
		// variables:


		$newTable = new \App\Table\PollResponse();
		$oldTable = new \Tests\Table\PollResponse();

		$this->setToMySQL();
		$expected = $oldTable->setMyVotesQuery();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->setMyVotesQuery();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->getDataObjectCursor(), $actual->getDataObjectCursor(), __METHOD__);
		}

	public function testSetVotersQuery() : void
		{
		// parameters: int $pollId
		// test type: static
		// variables: $pollId

		$pollId = 18;

		$newTable = new \App\Table\PollResponse();
		$oldTable = new \Tests\Table\PollResponse();

		$this->setToMySQL();
		$expected = $oldTable->setVotersQuery($pollId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->setVotersQuery($pollId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->getDataObjectCursor(), $actual->getDataObjectCursor(), __METHOD__);
		}
	}
