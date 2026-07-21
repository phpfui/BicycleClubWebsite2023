<?php

namespace Tests\SQLite;

class InvoiceItemTest extends \Tests\SQLAsserts
	{
	public function testFindItems() : void
		{
		// parameters: int $invoiceId, string $restrict, string $exclude, string $text
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $invoiceId, $restrict, $exclude, $text

		$invoiceId = 0;
		$restrict = '';
		$exclude = '';
		$text = 'jersey';

		$newTable = new \App\Table\InvoiceItem();
		$oldTable = new \Tests\Table\InvoiceItem();

		$this->setToMySQL();
		$expected = $oldTable->findItems($invoiceId, $restrict, $exclude, $text);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->findItems($invoiceId, $restrict, $exclude, $text);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetByDateType() : void
		{
		// parameters: string $startDate, string $endDate, array $types = []
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $startDate, $endDate, $types

		$startDate = '2025-01-01';
		$endDate = '2025-12-31';
		$types = [];

		$newTable = new \App\Table\InvoiceItem();
		$oldTable = new \Tests\Table\InvoiceItem();

		$this->setToMySQL();
		$expected = $oldTable->getByDateType($startDate, $endDate, $types);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getByDateType($startDate, $endDate, $types);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetUnshippedItems() : void
		{
		// parameters:
		// test type: \PHPFUI\ORM\ArrayCursor
		// variables:


		$newTable = new \App\Table\InvoiceItem();
		$oldTable = new \Tests\Table\InvoiceItem();

		$this->setToMySQL();
		$expected = $oldTable->getUnshippedItems();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getUnshippedItems();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertArrayCursorEquals($expected, $actual, __METHOD__);
		}
	}
