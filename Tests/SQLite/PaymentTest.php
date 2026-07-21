<?php

namespace Tests\SQLite;

class PaymentTest extends \Tests\SQLAsserts
	{
	public function testGetByDate() : void
		{
		// parameters: string $startDate, string $endDate, array $paymentTypes = [], bool $userOnly = false
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $startDate, $endDate, $paymentTypes, $userOnly

		$startDate = '2026-01-01';
		$endDate = '2027-01-01';
		$paymentTypes = [];
		$userOnly = false;

		$newTable = new \App\Table\Payment();
		$oldTable = new \Tests\Table\Payment();

		$this->setToMySQL();
		$expected = $oldTable->getByDate($startDate, $endDate, $paymentTypes, $userOnly);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getByDate($startDate, $endDate, $paymentTypes, $userOnly);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetPaymentTypes() : void
		{
		// parameters:
		// test type: array
		// variables:


		$newTable = new \App\Table\Payment();
		$oldTable = new \Tests\Table\Payment();

		$this->setToMySQL();
		$expected = $oldTable->getPaymentTypes();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getPaymentTypes();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}
	}
