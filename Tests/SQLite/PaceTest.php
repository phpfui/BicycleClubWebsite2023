<?php

namespace Tests\SQLite;

class PaceTest extends \Tests\SQLAsserts
	{
	public function testGetCategoryIdFromPaceId() : void
		{
		// parameters: ?int $paceId
		// test type: int
		// variables: $paceId

		$paceId = 51;

		$newTable = new \App\Table\Pace();
		$oldTable = new \Tests\Table\Pace();

		$this->setToMySQL();
		$expected = $oldTable->getCategoryIdFromPaceId($paceId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getCategoryIdFromPaceId($paceId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testGetPace() : void
		{
		// parameters: int $paceId
		// test type: string
		// variables: $paceId

		$paceId = 51;

		$newTable = new \App\Table\Pace();
		$oldTable = new \Tests\Table\Pace();

		$this->setToMySQL();
		$expected = $oldTable->getPace($paceId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getPace($paceId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testGetPaceOrder() : void
		{
		// parameters: int $categoryId
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $categoryId

		$categoryId = 6;

		$newTable = new \App\Table\Pace();
		$oldTable = new \Tests\Table\Pace();

		$this->setToMySQL();
		$expected = $oldTable->getPaceOrder($categoryId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getPaceOrder($categoryId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetPaces() : void
		{
		// parameters:
		// test type: array
		// variables:


		$newTable = new \App\Table\Pace();
		$oldTable = new \Tests\Table\Pace();

		$this->setToMySQL();
		$expected = $oldTable->getPaces();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getPaces();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testGetPacesForCategories() : void
		{
		// parameters: array $categories
		// test type: array
		// variables: $categories

		$categories = [1, 5, 9];

		$newTable = new \App\Table\Pace();
		$oldTable = new \Tests\Table\Pace();

		$this->setToMySQL();
		$expected = $oldTable->getPacesForCategories($categories);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getPacesForCategories($categories);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}
	}
