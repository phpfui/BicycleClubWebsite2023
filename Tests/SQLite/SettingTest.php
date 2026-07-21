<?php

namespace Tests\SQLite;

class SettingTest extends \Tests\SQLAsserts
	{
	public function testGetStandardPermissionGroup() : void
		{
		// parameters: string $name
		// test type: ?\App\Record\Permission
		// variables: $name

		$name = 'Normal Member';

		$newTable = new \App\Table\Setting();
		$oldTable = new \Tests\Table\Setting();

		$this->setToMySQL();
		$expected = $oldTable->getStandardPermissionGroup($name);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getStandardPermissionGroup($name);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->toArray(), $actual->toArray(), __METHOD__);
		}

	public function testSave() : void
		{
		// parameters: string $name, string | int $value
		// test type: static
		// variables: $name, $value

		$name = 'Test';
		$value = 'Testing';

		$newTable = new \App\Table\Setting();
		$oldTable = new \Tests\Table\Setting();

		$this->setToMySQL();
		$expected = $oldTable->save($name, $value);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->save($name, $value);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToMySQL();
		$expected = $oldTable->value($name);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->value($name);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $value, __METHOD__);
		$this->assertEquals($actual, $value, __METHOD__);
		}
	}
