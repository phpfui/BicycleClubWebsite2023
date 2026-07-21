<?php

namespace Tests\SQLite;

class BoardMemberTest extends \Tests\SQLAsserts
	{
	public function testGetBoardMembers() : void
		{
		// parameters:
		// test type: \PHPFUI\ORM\RecordCursor
		// variables:


		$newTable = new \App\Table\BoardMember();
		$oldTable = new \Tests\Table\BoardMember();

		$this->setToMySQL();
		$expected = $oldTable->getBoardMembers();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getBoardMembers();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetPosition() : void
		{
		// parameters: string $position
		// test type: \PHPFUI\ORM\DataObject
		// variables: $position

		$position = '';

		$newTable = new \App\Table\BoardMember();
		$oldTable = new \Tests\Table\BoardMember();

		$this->setToMySQL();
		$expected = $oldTable->getPosition($position);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getPosition($position);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->toArray(), $actual->toArray(), __METHOD__);
		}
	}
