<?php

namespace Tests\SQLite;

class PhotoTagTest extends \Tests\SQLAsserts
	{
	public function testGetHighestRight() : void
		{
		// parameters: int $photoId, int $row
		// test type: int
		// variables: $photoId, $row

		$photoId = 8909;
		$row = 1;

		$newTable = new \App\Table\PhotoTag();
		$oldTable = new \Tests\Table\PhotoTag();

		$this->setToMySQL();
		$expected = $oldTable->getHighestRight($photoId, $row);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getHighestRight($photoId, $row);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testGetTagsForPhoto() : void
		{
		// parameters: int $photoId
		// test type: \PHPFUI\ORM\ArrayCursor
		// variables: $photoId

		$photoId = 8909;

		$newTable = new \App\Table\PhotoTag();
		$oldTable = new \Tests\Table\PhotoTag();

		$this->setToMySQL();
		$expected = $oldTable->getTagsForPhoto($photoId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getTagsForPhoto($photoId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertArrayCursorEquals($expected, $actual, __METHOD__);
		}

	public function testMostTagged() : void
		{
		// parameters:
		// test type: \PHPFUI\ORM\ArrayCursor
		// variables:


		$newTable = new \App\Table\PhotoTag();
		$oldTable = new \Tests\Table\PhotoTag();

		$this->setToMySQL();
		$expected = $oldTable->mostTagged();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->mostTagged();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertArrayCursorEquals($expected, $actual, __METHOD__);
		}

	public function testTopTaggers() : void
		{
		// parameters:
		// test type: \PHPFUI\ORM\ArrayCursor
		// variables:


		$newTable = new \App\Table\PhotoTag();
		$oldTable = new \Tests\Table\PhotoTag();

		$this->setToMySQL();
		$expected = $oldTable->topTaggers();
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->topTaggers();
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertArrayCursorEquals($expected, $actual, __METHOD__);
		}
	}
