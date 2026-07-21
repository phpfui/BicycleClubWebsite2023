<?php

namespace Tests\SQLite;

class UserPermissionTest extends \Tests\SQLAsserts
	{
	public function testGetPermissionsForUser() : void
		{
		// parameters: int $memberId
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $memberId

		$memberId = 2590;

		$newTable = new \App\Table\UserPermission();
		$oldTable = new \Tests\Table\UserPermission();

		$this->setToMySQL();
		$expected = $oldTable->getPermissionsForUser($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getPermissionsForUser($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}
	}
