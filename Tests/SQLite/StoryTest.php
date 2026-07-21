<?php

namespace Tests\SQLite;

class StoryTest extends \Tests\SQLAsserts
	{
	public function testSetAllStoriesOnBlog() : void
		{
		// parameters: string $pageName
		// test type: static
		// variables: $pageName

		$pageName = 'Tech Corner';

		$newTable = new \App\Table\Story();
		$oldTable = new \Tests\Table\Story();

		$this->setToMySQL();
		$expected = $oldTable->setAllStoriesOnBlog($pageName);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->setAllStoriesOnBlog($pageName);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->getRecordCursor(), $actual->getRecordCursor(), __METHOD__);
		}

	public function testSetStoriesToPurge() : void
		{
		// parameters: string $date
		// test type: static
		// variables: $date

		$date = '2020-01-01';

		$newTable = new \App\Table\Story();
		$oldTable = new \Tests\Table\Story();

		$this->setToMySQL();
		$expected = $oldTable->setStoriesToPurge($date);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->setStoriesToPurge($date);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected->getRecordCursor(), $actual->getRecordCursor(), __METHOD__);
		}
	}
