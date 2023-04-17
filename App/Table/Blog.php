<?php

namespace App\Table;

class Blog extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\Blog::class;

	public static function getBlogsByName() : iterable
		{
		$sql = 'select * from blog order by name';

		return \PHPFUI\ORM::getDataObjectCursor($sql, []);
		}

	public static function getBlogsByNameForStory(int $storyId) : iterable
		{
		$sql = 'select b.*,bi.storyId from blog b left outer join blogItem bi on bi.blogId=b.blogId and bi.storyId=? order by name';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$storyId]);
		}

	public static function getNewestStory(string $blogname) : array
		{
		$sql = 'select s.* from story s
			inner join blog b on b.name=?
			inner join blogItem bi on b.blogId=bi.blogId
			where s.storyId=bi.storyId and s.date>0 order by s.date desc limit 1';

		return \PHPFUI\ORM::getRow($sql, [$blogname]);
		}

	public static function getOldest(string $blogname) : string
		{
		$sql = 'select s.date from story s
			inner join blog b on b.name=?
			inner join blogItem bi on b.blogId=bi.blogId
			where s.storyId=bi.storyId and s.date>0 order by s.date limit 1';

		return \PHPFUI\ORM::getValue($sql, [$blogname]);
		}

	public static function getStoriesForBlog(string $pageName, bool $signedIn = false, int $year = 0) : iterable
		{
		$today = \App\Tools\Date::todayString();
		$sql = 'select s.*,b.blogId,bi.ranking from story s
			inner join blogItem bi on s.storyId=bi.storyId
			inner join blog b on b.blogId=bi.blogId
			where b.name=? and (s.startDate<=? or s.startDate is null) and (s.endDate>=? or s.endDate is null)';

		if (! $signedIn)
			{
			$sql .= ' and (s.membersOnly=0)';
			}
		$input = [$pageName, $today, $today, ];

		if ($year)
			{
			$sql .= ' and s.date>=? and s.date<=?';
			$input[] = "{$year}-01-01";
			$input[] = "{$year}-12-31";
			}
		$sql .= ' order by s.onTop desc, bi.ranking';

		if ($year)
			{
			$sql .= ',s.date desc';
			}

		return \PHPFUI\ORM::getDataObjectCursor($sql, $input);
		}

	public static function renumberBlog(int $blogId)
		{
		$sql = 'SET @ordering_inc = 1;SET @new_ordering = 0;UPDATE blogItem SET ranking = (@new_ordering := @new_ordering + @ordering_inc) WHERE blogId=? ORDER BY ranking;';

		return \PHPFUI\ORM::execute($sql, [$blogId]);
		}
	}
