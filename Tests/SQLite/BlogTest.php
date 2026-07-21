<?php

namespace Tests\SQLite;

class BlogTest extends \Tests\SQLAsserts
	{
	public function testGetBlogsByNameForStory() : void
		{
		// parameters: int $storyId
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $storyId

		$storyId = 918;

		$newTable = new \App\Table\Blog();
		$oldTable = new \Tests\Table\Blog();

		$this->setToMySQL();
		$expected = $oldTable->getBlogsByNameForStory($storyId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getBlogsByNameForStory($storyId);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}

	public function testGetNewestStory() : void
		{
		// parameters: string $blogname
		// test type: array
		// variables: $blogname

		$blogname = '';

		$newTable = new \App\Table\Blog();
		$oldTable = new \Tests\Table\Blog();

		$this->setToMySQL();
		$expected = $oldTable->getNewestStory($blogname);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getNewestStory($blogname);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testGetOldest() : void
		{
		// parameters: string $blogname
		// test type: string
		// variables: $blogname

		$blogname = '';

		$newTable = new \App\Table\Blog();
		$oldTable = new \Tests\Table\Blog();

		$this->setToMySQL();
		$expected = $oldTable->getOldest($blogname);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getOldest($blogname);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertEquals($expected, $actual, __METHOD__);
		}

	public function testGetStoriesForBlog() : void
		{
		// parameters: \App\Record\Blog $blog, bool $signedIn = false, int $year = 0
		// test type: \PHPFUI\ORM\DataObjectCursor
		// variables: $blog, $signedIn, $year

		$blog = new \App\Record\Blog(['name' => 'Board News']);
		$signedIn = true;
		$year = 2009;

		$newTable = new \App\Table\Blog();
		$oldTable = new \Tests\Table\Blog();

		$this->setToMySQL();
		$expected = $oldTable->getStoriesForBlog($blog, $signedIn, $year);
		$this->assertNoSQLErrors(__METHOD__);

		$this->setToSQLite();
		$actual = $newTable->getStoriesForBlog($blog, $signedIn, $year);
		$this->assertNoSQLErrors(__METHOD__);

		$this->assertDataObjectCursorEquals($expected, $actual, __METHOD__);
		}
	}
