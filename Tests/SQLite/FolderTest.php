<?php

namespace Tests\SQLite;

class FolderTest extends \Tests\SQLAsserts
	{
	public function testGetParentFolders() : void
		{
		// parameters: int $folderId
		// test type: array
		// variables: $folderId

		$folderId = 10;

		$newTable = new \App\Table\Folder();
		$oldTable = new \Tests\Table\Folder();

		$this->setToMySQL();
		$expected = $oldTable->getParentFolders($folderId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getParentFolders($folderId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}
	}
