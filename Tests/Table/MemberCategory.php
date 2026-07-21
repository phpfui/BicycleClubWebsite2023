<?php

namespace Tests\Table;

class MemberCategory extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\MemberCategory::class;

	/**
	 * @return array<int,int>
	 */
	public static function getRideCategoriesForMember(?int $memberId) : array
		{
		$sql = 'select categoryId from memberCategory where memberId=?';
		$cats = \PHPFUI\ORM::getDataObjectCursor($sql, [(int)$memberId]);
		$returnValue = [];

		foreach ($cats as $row)
			{
			$returnValue[$row->categoryId] = (int)$row->categoryId;
			}

		return $returnValue;
		}

	public static function getRideCategoryStringForMember(int $memberId) : string
		{
		$sql = 'select group_concat(category) from category c left join memberCategory mc on mc.categoryId = c.categoryId where mc.memberId=? order by ordering';

		return \PHPFUI\ORM::getValue($sql, [$memberId]);
		}
	}
