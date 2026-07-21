<?php

namespace Tests\SQLite;

class CartItemTest extends \Tests\SQLAsserts
	{
	public function testGetCartFor() : void
		{
		// parameters: int $memberId
		// test type: array
		// variables: $memberId

		$memberId = 7193;

		$newTable = new \App\Table\CartItem();
		$oldTable = new \Tests\Table\CartItem();

		$this->setToMySQL();
		$expected = $oldTable->getCartFor($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getCartFor($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testGetItemCountForMember() : void
		{
		// parameters: string $validItemNumbers, int $customerNumber
		// test type: int
		// variables: $validItemNumbers, $customerNumber

		$validItemNumbers = '47';
		$customerNumber = 7193;

		$newTable = new \App\Table\CartItem();
		$oldTable = new \Tests\Table\CartItem();

		$this->setToMySQL();
		$expected = $oldTable->getItemCountForMember($validItemNumbers, $customerNumber);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getItemCountForMember($validItemNumbers, $customerNumber);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}
	}
