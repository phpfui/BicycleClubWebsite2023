<?php

namespace Tests\SQLite;

class InvoiceTest extends \Tests\SQLAsserts
	{
	public function testFind() : void
		{
		// parameters: array $parameters
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $parameters

		$parameters = [
			//invoiceId=&paypaltx=&text=&name=&startDate=2024-07-06&endDate=2024-08-01&shipped=0&submit=Search
			'startDate' => '2024-07-06',
			'endDate' => '2024-08-01',
		];

		$newTable = new \App\Table\Invoice();
		$oldTable = new \Tests\Table\Invoice();

		$this->setToMySQL();
		$expected = $oldTable->find($parameters);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->find($parameters);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetByDateType() : void
		{
		// parameters: string $startDate, string $endDate, array $types = []
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $startDate, $endDate, $types

		$startDate = '2024-07-06';
		$endDate = '2024-08-01';
		$types = [\App\Enum\Store\Type::MEMBERSHIP->value];

		$newTable = new \App\Table\Invoice();
		$oldTable = new \Tests\Table\Invoice();

		$this->setToMySQL();
		$expected = $oldTable->getByDateType($startDate, $endDate, $types);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getByDateType($startDate, $endDate, $types);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetPaidByDate() : void
		{
		// parameters: int $shipped, string $startDate = '', string $endDate = '', int $points = 0
		// test type: \PHPFUI\ORM\ArrayCursor
		// variables: $shipped, $startDate, $endDate, $points

		$shipped = 1;
		$startDate = '2024-07-06';
		$endDate = '2024-08-01';
		$points = 0;

		$newTable = new \App\Table\Invoice();
		$oldTable = new \Tests\Table\Invoice();

		$this->setToMySQL();
		$expected = $oldTable->getPaidByDate($shipped, $startDate, $endDate, $points);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getPaidByDate($shipped, $startDate, $endDate, $points);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertArrayCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetTaxes() : void
		{
		// parameters: string $startDate, string $endDate
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $startDate, $endDate

		$startDate = '2024-07-06';
		$endDate = '2024-08-01';

		$newTable = new \App\Table\Invoice();
		$oldTable = new \Tests\Table\Invoice();

		$this->setToMySQL();
		$expected = $oldTable->getTaxes($startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getTaxes($startDate, $endDate);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetUnpaidBefore() : void
		{
		// parameters: string $date
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $date

		$date = '2024-07-06';

		$newTable = new \App\Table\Invoice();
		$oldTable = new \Tests\Table\Invoice();

		$this->setToMySQL();
		$expected = $oldTable->getUnpaidBefore($date);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getUnpaidBefore($date);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetUnpaidOn() : void
		{
		// parameters: array $dates
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $dates

		$dates = ['2024-07-06'];

		$newTable = new \App\Table\Invoice();
		$oldTable = new \Tests\Table\Invoice();

		$this->setToMySQL();
		$expected = $oldTable->getUnpaidOn($dates);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getUnpaidOn($dates);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testPointsUsed() : void
		{
		// parameters: string $start, string $end, string $sort
		// test type: \PHPFUI\ORM\ArrayCursor
		// variables: $start, $end, $sort

		$start = '2024-07-06';
		$end = '2024-08-01';

		$newTable = new \App\Table\Invoice();
		$oldTable = new \Tests\Table\Invoice();

		$this->setToMySQL();
		$expected = $oldTable->pointsUsed($start, $end);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->pointsUsed($start, $end);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertArrayCursorEquals($expected, $actual, __METHOD__, 'memberId');
		}

	public function testSetCompletedForMember() : void
		{
		// parameters: int $memberId
		// test type: static
		// variables: $memberId

		$memberId = 2950;

		$newTable = new \App\Table\Invoice();
		$oldTable = new \Tests\Table\Invoice();

		$this->setToMySQL();
		$expected = $oldTable->setCompletedForMember($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->setCompletedForMember($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected->getDataObjectCursor(), $actual->getDataObjectCursor(), __METHOD__);
		}

	public function testSetUnpaidForMember() : void
		{
		// parameters: int $memberId
		// test type: static
		// variables: $memberId

		$memberId = 2950;

		$newTable = new \App\Table\Invoice();
		$oldTable = new \Tests\Table\Invoice();

		$this->setToMySQL();
		$expected = $oldTable->setUnpaidForMember($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->setUnpaidForMember($memberId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected->getDataObjectCursor(), $actual->getDataObjectCursor(), __METHOD__);
		}

	public function testSetUnrecordedChecks() : void
		{
		// parameters:
		// test type: static
		// variables:


		$newTable = new \App\Table\Invoice();
		$oldTable = new \Tests\Table\Invoice();

		$this->setToMySQL();
		$expected = $oldTable->setUnrecordedChecks();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->setUnrecordedChecks();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected->getDataObjectCursor(), $actual->getDataObjectCursor(), __METHOD__);
		}

	public function testSetUnshippedInvoices() : void
		{
		// parameters:
		// test type: static
		// variables:


		$newTable = new \App\Table\Invoice();
		$oldTable = new \Tests\Table\Invoice();

		$this->setToMySQL();
		$expected = $oldTable->setUnshippedInvoices();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->setUnshippedInvoices();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected->getDataObjectCursor(), $actual->getDataObjectCursor(), __METHOD__);
		}
	}
