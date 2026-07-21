<?php

namespace Tests\SQLite;

class CategoryTest extends \Tests\SQLAsserts
	{
	public function testGetAllCategories() : void
		{
		// parameters:
		// test type: \PHPFUI\ORM\RecordCursor
		// variables:


		$newTable = new \App\Table\Category();
		$oldTable = new \Tests\Table\Category();

		$this->setToMySQL();
		$expected = $oldTable->getAllCategories();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getAllCategories();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetCategoryForId() : void
		{
		// parameters: int $categoryId
		// test type: string
		// variables: $categoryId

		$categoryId = 5;

		$newTable = new \App\Table\Category();
		$oldTable = new \Tests\Table\Category();

		$this->setToMySQL();
		$expected = $oldTable->getCategoryForId($categoryId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getCategoryForId($categoryId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testGetDefaults() : void
		{
		// parameters:
		// test type: array
		// variables:


		$newTable = new \App\Table\Category();
		$oldTable = new \Tests\Table\Category();

		$this->setToMySQL();
		$expected = $oldTable->getDefaults();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getDefaults();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testGetDistributions() : void
		{
		// parameters:
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables:


		$newTable = new \App\Table\Category();
		$oldTable = new \Tests\Table\Category();

		$this->setToMySQL();
		$expected = $oldTable->getDistributions();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getDistributions();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}
	}
