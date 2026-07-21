<?php

namespace Tests\SQLite;

class PermissionGroupTest extends \Tests\SQLAsserts
	{
	public function testGetGroupPermissions() : void
		{
		// parameters: int $groupId
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $groupId

		$groupId = 2;

		$newTable = new \App\Table\PermissionGroup();
		$oldTable = new \Tests\Table\PermissionGroup();

		$this->setToMySQL();
		$expected = $oldTable->getGroupPermissions($groupId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getGroupPermissions($groupId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetPermissionsForGroup() : void
		{
		// parameters: int $groupId
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $groupId

		$groupId = 2;

		$newTable = new \App\Table\PermissionGroup();
		$oldTable = new \Tests\Table\PermissionGroup();

		$this->setToMySQL();
		$expected = $oldTable->getPermissionsForGroup($groupId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getPermissionsForGroup($groupId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}
	}
