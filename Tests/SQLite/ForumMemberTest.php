<?php

namespace Tests\SQLite;

class ForumMemberTest extends \Tests\SQLAsserts
	{
	public function testGetCount() : void
		{
		// parameters: \App\Record\Forum $forum
		// test type: int
		// variables: $forum

		$forum = new \App\Record\Forum(4);

		$newTable = new \App\Table\ForumMember();
		$oldTable = new \Tests\Table\ForumMember();

		$this->setToMySQL();
		$expected = $oldTable->getCount($forum);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getCount($forum);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testGetDigestMembers() : void
		{
		// parameters: \App\Record\Forum $forum
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $forum

		$forum = new \App\Record\Forum(4);

		$newTable = new \App\Table\ForumMember();
		$oldTable = new \Tests\Table\ForumMember();

		$this->setToMySQL();
		$expected = $oldTable->getDigestMembers($forum);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getDigestMembers($forum);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetEmailMembers() : void
		{
		// parameters: \App\Record\Forum $forum
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $forum

		$forum = new \App\Record\Forum(4);

		$newTable = new \App\Table\ForumMember();
		$oldTable = new \Tests\Table\ForumMember();

		$this->setToMySQL();
		$expected = $oldTable->getEmailMembers($forum);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getEmailMembers($forum);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetMembers() : void
		{
		// parameters: \App\Record\Forum $forum, array $additionalWhere = []
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $forum, $additionalWhere

		$forum = new \App\Record\Forum(4);
		$additionalWhere = [];

		$newTable = new \App\Table\ForumMember();
		$oldTable = new \Tests\Table\ForumMember();

		$this->setToMySQL();
		$expected = $oldTable->getMembers($forum, $additionalWhere);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getMembers($forum, $additionalWhere);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testSetMembersQuery() : void
		{
		// parameters: \App\Record\Forum $forum, array $additionalWhere = []
		// test type: self
		// variables: $forum, $additionalWhere

		$forum = new \App\Record\Forum(4);
		$additionalWhere = [];

		$newTable = new \App\Table\ForumMember();
		$oldTable = new \Tests\Table\ForumMember();

		$this->setToMySQL();
		$expected = $oldTable->setMembersQuery($forum, $additionalWhere);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->setMembersQuery($forum, $additionalWhere);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected->getRecordCursor(), $actual->getRecordCursor(), __METHOD__);
		}
	}
