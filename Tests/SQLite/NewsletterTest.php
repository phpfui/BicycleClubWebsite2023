<?php

namespace Tests\SQLite;

class NewsletterTest extends \Tests\SQLAsserts
	{
	public function testGetAllByYear() : void
		{
		// parameters: int $year
		// test type: \PHPFUI\ORM\RecordCursor
		// variables: $year

		$year = 2025;

		$newTable = new \App\Table\Newsletter();
		$oldTable = new \Tests\Table\Newsletter();

		$this->setToMySQL();
		$expected = $oldTable->getAllByYear($year);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getAllByYear($year);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetFirst() : void
		{
		// parameters: string $ascending = ''
		// test type: \App\Record\Newsletter
		// variables: $ascending

		$ascending = '';

		$newTable = new \App\Table\Newsletter();
		$oldTable = new \Tests\Table\Newsletter();

		$this->setToMySQL();
		$expected = $oldTable->getFirst($ascending);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getFirst($ascending);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->toArray(), $actual->toArray(), __METHOD__);

		$ascending = 'desc';
		$newTable = new \App\Table\Newsletter();
		$oldTable = new \Tests\Table\Newsletter();

		$this->setToMySQL();
		$expected = $oldTable->getFirst($ascending);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getFirst($ascending);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->toArray(), $actual->toArray(), __METHOD__);
		}

	public function testGetLatest() : void
		{
		// parameters:
		// test type: \App\Record\Newsletter
		// variables:


		$newTable = new \App\Table\Newsletter();
		$oldTable = new \Tests\Table\Newsletter();

		$this->setToMySQL();
		$expected = $oldTable->getLatest();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getLatest();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected->toArray(), $actual->toArray(), __METHOD__);
		}
	}
