<?php

namespace Tests\SQLite;

class PermissionTest extends \Tests\SQLAsserts
	{
	public function testGetAllPermissionGroups() : void
		{
		// parameters:
		// test type: \PHPFUI\ORM\RecordCursor
		// variables:


		$newTable = new \App\Table\Permission();
		$oldTable = new \Tests\Table\Permission();

		$this->setToMySQL();
		$expected = $oldTable->getAllPermissionGroups();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getAllPermissionGroups();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetAllPermissions() : void
		{
		// parameters: string $column = 'name', string $sort = 'a', int $page = 0, int $limit = 0
		// test type: \PHPFUI\ORM\ArrayCursor
		// variables: $column, $sort, $page, $limit

		$column = 'name';
		$sort = 'd';
		$page = '10';
		$limit = '20';

		$newTable = new \App\Table\Permission();
		$oldTable = new \Tests\Table\Permission();

		$this->setToMySQL();
		$expected = $oldTable->getAllPermissions($column, $sort, $page, $limit);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getAllPermissions($column, $sort, $page, $limit);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertArrayCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetAllPermissionsCount() : void
		{
		// parameters:
		// test type: int
		// variables:


		$newTable = new \App\Table\Permission();
		$oldTable = new \Tests\Table\Permission();

		$this->setToMySQL();
		$expected = $oldTable->getAllPermissionsCount();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getAllPermissionsCount();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testGetMembersWithPermissionGroup() : void
		{
		// parameters: string $name
		// test type: ?\PHPFUI\ORM\RecordCursor
		// variables: $name

		$name = 'Normal Member';

		$newTable = new \App\Table\Permission();
		$oldTable = new \Tests\Table\Permission();

		$this->setToMySQL();
		$expected = $oldTable->getMembersWithPermissionGroup($name);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getMembersWithPermissionGroup($name);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetNextGroupId() : void
		{
		// parameters:
		// test type: int
		// variables:


		$newTable = new \App\Table\Permission();
		$oldTable = new \Tests\Table\Permission();

		$this->setToMySQL();
		$expected = $oldTable->getNextGroupId();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getNextGroupId();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testGetNextPermissionId() : void
		{
		// parameters:
		// test type: int
		// variables:


		$newTable = new \App\Table\Permission();
		$oldTable = new \Tests\Table\Permission();

		$this->setToMySQL();
		$expected = $oldTable->getNextPermissionId();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getNextPermissionId();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}
	}
