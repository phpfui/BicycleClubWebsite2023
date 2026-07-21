<?php

namespace Tests\SQLite;

class FileTest extends \Tests\SQLAsserts
	{
	public function testSearch() : void
		{
		// parameters: array $parameters = []
		// test type: static
		// variables: $parameters

		$parameters = ['fileName' => 'minutes'];

		$newTable = new \App\Table\File();
		$oldTable = new \Tests\Table\File();

		$this->setToMySQL();
		$expected = $oldTable->search($parameters);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->search($parameters);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected->getRecordCursor(), $actual->getRecordCursor(), __METHOD__);
		}
	}
