<?php

namespace App\Table;

class Blog extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\Blog::class;

	public function getBlogsByNameForStory(int $storyId) : \PHPFUI\ORM\DataObjectCursor
		{
		$joinCondition = new \PHPFUI\ORM\Condition('blogItem.blogId', new \PHPFUI\ORM\Literal('blog.blogId'));
		$joinCondition->and('blogItem.storyId', $storyId);
		$this->setJoin('blogItem', $joinCondition, 'left outer');
		$this->setSelect('blog.*');
		$this->addSelect('blogItem.storyId');
		$this->setOrderBy('name');

		return $this->getDataObjectCursor();
		}

	/**
	 * @return array<string,string>
	 */
	public static function getNewestStory(string $blogname) : array
		{
		$sql = 'select s.* from story s
			inner join blog b on b.name=?
			inner join blogItem bi on b.blogId=bi.blogId
			where s.storyId=bi.storyId and s.date>0
			order by s.date desc limit 1';

		return \PHPFUI\ORM::getRow($sql, [$blogname]);
		}

	public static function getOldest(string $blogname) : string
		{
		$sql = 'select s.date from story s
			inner join blog b on b.name=?
			inner join blogItem bi on b.blogId=bi.blogId
			where s.storyId=bi.storyId and s.date>0
			order by s.date limit 1';

		return \PHPFUI\ORM::getValue($sql, [$blogname]);
		}

	public function getStoriesForBlog(\App\Record\Blog $blog, bool $signedIn = false, int $year = 0) : \PHPFUI\ORM\DataObjectCursor
		{
		$today = \App\Tools\Date::todayString();
		$storyTable = new \App\Table\Story();
		$storyTable->setSelect('story.*');
		$storyTable->addSelect('blog.blogId');
		$storyTable->addSelect('blogItem.*');

		$storyTable->setJoin('blogItem');
		$storyTable->addJoin('blog', new \PHPFUI\ORM\Condition('blogItem.blogId', new \PHPFUI\ORM\Literal('blog.blogId')));
		$condition = new \PHPFUI\ORM\Condition('blog.blogId', $blog->blogId);

		$orCondition = new \PHPFUI\ORM\Condition('startDate', $today, new \PHPFUI\ORM\Operator\LessThanEqual());
		$orCondition->or('startDate', null);
		$condition->and($orCondition);

		$orCondition = new \PHPFUI\ORM\Condition('endDate', $today, new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$orCondition->or('endDate', null);
		$condition->and($orCondition);

		if (! $signedIn)
			{
			$condition->and('blogItem.membersOnly', 0);
			}

		if ($year)
			{
			$condition->and('story.date', "{$year}-01-01", new \PHPFUI\ORM\Operator\GreaterThanEqual());
			$condition->and('story.date', "{$year}-12-31", new \PHPFUI\ORM\Operator\LessThanEqual());
			}
		$storyTable->setWhere($condition);
		$storyTable->setOrderBy('blogItem.onTop', 'desc');
		$storyTable->addOrderBy('blogItem.ranking');

		if ($year)
			{
			$storyTable->addOrderBy('story.date', 'desc');
			}

		return $storyTable->getDataObjectCursor();
		}

	public static function renumberBlog(int $blogId) : bool
		{
		$sql = 'SET @ordering_inc = 1;SET @new_ordering = 0;UPDATE blogItem SET ranking = (@new_ordering := @new_ordering + @ordering_inc) WHERE blogId=? ORDER BY ranking;';

		return \PHPFUI\ORM::execute($sql, [$blogId]);
		}
	}
