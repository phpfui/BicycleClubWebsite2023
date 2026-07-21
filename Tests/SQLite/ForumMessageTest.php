<?php

namespace Tests\SQLite;

class ForumMessageTest extends \Tests\SQLAsserts
	{
	public function testGetNextMessage() : void
		{
		// parameters: \App\Record\ForumMessage $message
		// test type: \App\Record\ForumMessage
		// variables: $message

		$message = new \App\Record\ForumMessage(14880);

		$newTable = new \App\Table\ForumMessage();
		$oldTable = new \Tests\Table\ForumMessage();

		$this->setToMySQL();
		$expected = $oldTable->getNextMessage($message);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getNextMessage($message);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->toArray(), $actual->toArray(), __METHOD__);
		}

	public function testGetPreviousMessage() : void
		{
		// parameters: \App\Record\ForumMessage $message
		// test type: \App\Record\ForumMessage
		// variables: $message

		$message = new \App\Record\ForumMessage(14880);

		$newTable = new \App\Table\ForumMessage();
		$oldTable = new \Tests\Table\ForumMessage();

		$this->setToMySQL();
		$expected = $oldTable->getPreviousMessage($message);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getPreviousMessage($message);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->toArray(), $actual->toArray(), __METHOD__);
		}
	}
