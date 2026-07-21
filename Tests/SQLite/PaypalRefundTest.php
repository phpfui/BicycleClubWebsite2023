<?php

namespace Tests\SQLite;

class PaypalRefundTest extends \Tests\SQLAsserts
	{
	public function testGetPendingRefunds() : void
		{
		// parameters:
		// test type: \PHPFUI\ORM\RecordCursor
		// variables:


		$newTable = new \App\Table\PaypalRefund();
		$oldTable = new \Tests\Table\PaypalRefund();

		$this->setToMySQL();
		$expected = $oldTable->getPendingRefunds();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getPendingRefunds();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}
	}
