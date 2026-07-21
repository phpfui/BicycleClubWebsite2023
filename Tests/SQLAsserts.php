<?php

namespace Tests;

class SQLAsserts extends \PHPUnit\Framework\TestCase
	{
	private string $mySQLConnection;

	private ?\PHPFUI\ORM\PDOInstance $mysqlPDO;

	private string $sqLiteConnection;

	private ?\PHPFUI\ORM\PDOInstance $sqlitePDO;

	public function setUp() : void
		{
		\PHPFUI\ORM::$extendedFields = false;
		$this->mysqlPDO = new \PHPFUI\ORM\PDOInstance('mysql:host=localhost;user=root;dbname=wcc;port=3306');
		$this->mySQLConnection = \PHPFUI\ORM::addConnection($this->mysqlPDO, 'mysql');
//		$this->sqlitePDO = new \PHPFUI\ORM\PDOInstance('mysql:host=localhost;user=root;dbname=wcc;port=3306');
//		$this->sqLiteConnection = \PHPFUI\ORM::addConnection($this->mysqlPDO, 'mysql');
		$this->sqlitePDO = new \PHPFUI\ORM\PDOInstance('sqlite:Tests\data\db.sqlite');
		$this->sqlitePDO->sqliteCreateFunction('acos', 'acos', 1);
		$this->sqlitePDO->sqliteCreateFunction('cos', 'cos', 1);
		$this->sqlitePDO->sqliteCreateFunction('sin', 'sin', 1);
		$this->sqlitePDO->sqliteCreateFunction('radians', 'deg2rad', 1);
		$this->sqLiteConnection = \PHPFUI\ORM::addConnection($this->sqlitePDO, 'sqLite');
		}

	public function tearDown() : void
		{
		$this->mysqlPDO = null; $this->sqlitePDO = null;
		}

	public function assertArrayCursorEquals(\PHPFUI\ORM\ArrayCursor $expected, \PHPFUI\ORM\ArrayCursor $actual, string $testMethod, string $uniqueKey = '') : void
		{
		$this->setToMySQL();
		$this->assertCursorCounts($expected, $testMethod);

		$expectedData = [];

		foreach ($expected as $data)
			{
			$expectedData[] = $data;
			}
		$this->assertNoSQLErrors($testMethod);

		$actualData = [];
		$this->setToSQLite();
		$this->assertCursorCounts($actual, $testMethod);

		foreach ($actual as $data)
			{
			$actualData[] = $data;
			}
		$this->assertNoSQLErrors($testMethod);

		$this->assertEquals($expectedData, $actualData, "{$testMethod}: ArrayCursor data is not equal");
		}

	public function assertCursorCounts(\PHPFUI\ORM\BaseCursor $cursor, string $testMethod) : void
		{
		$count = \count($cursor);
		$this->assertNoSQLErrors($testMethod);
		$total = $cursor->total();
		$this->assertNoSQLErrors($testMethod);

		$this->assertGreaterThanOrEqual($count, $total, "{$testMethod}: total is less than count for " . \PHPFUI\ORM::getConnection());
		}

	public function assertDataObjectCursorEquals(\PHPFUI\ORM\DataObjectCursor $expected, \PHPFUI\ORM\DataObjectCursor $actual, string $testMethod, string $uniqueKey = '') : void
		{
		$this->setToMySQL();
		$this->assertEquals(\count($expected), \count($actual), 'Cursor counts not equal ' . $testMethod);
		$this->assertCursorCounts($expected, $testMethod);

		$expectedData = [];

		foreach ($expected as $data)
			{
			if ($uniqueKey)
				{
				$array = $data->toArray();
				$expectedData[$array[$uniqueKey]] = $array;
				}
			else
				{
				$expectedData[] = $data->toArray();
				}
			}
		$this->assertNoSQLErrors($testMethod);

		$actualData = [];
		$this->setToSQLite();
		$this->assertCursorCounts($actual, $testMethod);

		foreach ($actual as $data)
			{
			if ($uniqueKey)
				{
				$array = $data->toArray();
				$actualData[$array[$uniqueKey]] = $array;
				}
			else
				{
				$actualData[] = $data->toArray();
				}
			}
		$this->assertNoSQLErrors($testMethod);

		$this->assertEquals($expectedData, $actualData, "{$testMethod}: DataObjectCursor data is not equal");
		}

	public function assertNoSQLErrors(string $testMethod) : void
		{
		$message = \PHPFUI\ORM::getLastError();
		$sql = \PHPFUI\ORM::getLastSQL();
		$this->assertEmpty($message, "{$testMethod}: SQL Error: {$message} SQL: {$sql} for " . \PHPFUI\ORM::getConnection());
		\PHPFUI\ORM::getInstance()->clearErrors();
		}

	public function setToMySQL() : void
		{
		\PHPFUI\ORM::useConnection($this->mySQLConnection);
		}

	public function setToSQLite() : void
		{
		\PHPFUI\ORM::useConnection($this->sqLiteConnection);
		}
	}
