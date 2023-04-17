<?php

namespace App\Table;

class Story extends \PHPFUI\ORM\Table
{
	protected static string $className = '\\' . \App\Record\Story::class;

	public function setAllStoriesOnBlog(string $pageName) : static
		{
		$this->addJoin('blogItem', new \PHPFUI\ORM\Condition('blogItem.storyId', new \PHPFUI\ORM\Literal('story.storyId')));
		$this->addJoin('blog', new \PHPFUI\ORM\Condition('blog.blogId', new \PHPFUI\ORM\Literal('blogItem.blogId')));
		$this->setWhere(new \PHPFUI\ORM\Condition('blog.name', $pageName));
		$this->addOrderBy('onTop', 'desc');
		$this->addOrderBy('blogItem.ranking');

		return $this;
		}

	public function setStoriesToPurge(string $date) : static
		{
		$this->setWhere(new \PHPFUI\ORM\Condition('lastEdited', $date, new \PHPFUI\ORM\Operator\LessThan()));

		return $this;
		}

	public static function purgeStories(string $date) : bool
		{
		$sql = 'delete from story WHERE lastEdited<?';
		\PHPFUI\ORM::execute($sql, [$date]);

		$sql = 'delete from blogItem WHERE storyId not in (select storyId from story)';

		return \PHPFUI\ORM::execute($sql);
		}
	}
