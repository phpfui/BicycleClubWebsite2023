<?php

namespace Tests\SQLite;

class VideoTest extends \Tests\SQLAsserts
	{
	public function testSearch() : void
		{
		// parameters: array $parameters = []
		// test type: static
		// variables: $parameters

		$parameters = [
			'title' => 'website',
			'description' => '',
			'submit' => 'Search',
		];

		$newTable = new \App\Table\Video();
		$oldTable = new \Tests\Table\Video();

		$this->setToMySQL();
		$expected = $oldTable->search($parameters);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->search($parameters);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected->getRecordCursor(), $actual->getRecordCursor(), __METHOD__);
		}
	}
