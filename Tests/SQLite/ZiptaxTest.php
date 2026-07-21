<?php

namespace Tests\SQLite;

class ZiptaxTest extends \Tests\SQLAsserts
	{
	public function testGetTaxRateForZip() : void
		{
		// parameters: string $zip
		// test type: float
		// variables: $zip

		$zip = '10583';

		$newTable = new \App\Table\Ziptax();
		$oldTable = new \Tests\Table\Ziptax();

		$this->setToMySQL();
		$expected = $oldTable->getTaxRateForZip($zip);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getTaxRateForZip($zip);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}
	}
