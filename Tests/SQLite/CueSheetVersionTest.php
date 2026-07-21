<?php

namespace Tests\SQLite;

class CueSheetVersionTest extends \Tests\SQLAsserts
	{
	public function testSetDateDescCursor() : void
		{
		// parameters: \App\Record\CueSheet $cueSheet, int $limit = 0
		// test type: static
		// variables: $cueSheet, $limit

		$cueSheet = new \App\Record\CueSheet(2);
		$limit = 10;

		$newTable = new \App\Table\CueSheetVersion();
		$oldTable = new \Tests\Table\CueSheetVersion();

		$this->setToMySQL();
		$expected = $oldTable->setDateDescCursor($cueSheet, $limit);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->setDateDescCursor($cueSheet, $limit);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected->getRecordCursor(), $actual->getRecordCursor(), __METHOD__);
		}
	}
