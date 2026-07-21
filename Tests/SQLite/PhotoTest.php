<?php

namespace Tests\SQLite;

class PhotoTest extends \Tests\SQLAsserts
	{
	public function testSearch() : void
		{
		// parameters: array $parameters = []
		// test type: static
		// variables: $parameters

		$parameters = ['description' => 'logo'];

		$newTable = new \App\Table\Photo();
		$oldTable = new \Tests\Table\Photo();

		$this->setToMySQL();
		$expected = $oldTable->search($parameters);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->search($parameters);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected->getRecordCursor(), $actual->getRecordCursor(), __METHOD__);
		}
	}
