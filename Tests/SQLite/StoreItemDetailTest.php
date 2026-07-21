<?php

namespace Tests\SQLite;

class StoreItemDetailTest extends \Tests\SQLAsserts
	{
	public function testGetAllStock() : void
		{
		// parameters: int $storeItemId, string $order = 'storeItemDetailId'
		// test type: \PHPFUI\ORM\ArrayCursor
		// variables: $storeItemId, $order

		$storeItemId = 177;

		$newTable = new \App\Table\StoreItemDetail();
		$oldTable = new \Tests\Table\StoreItemDetail();

		$this->setToMySQL();
		$expected = $oldTable->getAllStock($storeItemId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getAllStock($storeItemId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertArrayCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetInStock() : void
		{
		// parameters: int $storeItemId, string $order = 'storeItemDetailId'
		// test type: \PHPFUI\ORM\ArrayCursor
		// variables: $storeItemId, $order

		$storeItemId = 179;

		$newTable = new \App\Table\StoreItemDetail();
		$oldTable = new \Tests\Table\StoreItemDetail();

		$this->setToMySQL();
		$expected = $oldTable->getInStock($storeItemId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getInStock($storeItemId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertArrayCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetOutOfStock() : void
		{
		// parameters: int $storeItemId, string $order = 'storeItemDetailId'
		// test type: \PHPFUI\ORM\ArrayCursor
		// variables: $storeItemId, $order

		$storeItemId = 177;

		$newTable = new \App\Table\StoreItemDetail();
		$oldTable = new \Tests\Table\StoreItemDetail();

		$this->setToMySQL();
		$expected = $oldTable->getOutOfStock($storeItemId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getOutOfStock($storeItemId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertArrayCursorEquals($expected, $actual, __METHOD__);
		}
	}
