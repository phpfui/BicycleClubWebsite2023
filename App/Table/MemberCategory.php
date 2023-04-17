<?php

namespace App\Table;

class MemberCategory extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\MemberCategory::class;

	public static function changeCategory($from, $to)
		{
		$sql = 'update memberCategory set categoryId=:to where categoryId=:from';

		return \PHPFUI\ORM::execute($sql, ['from' => $from,
			'to' => $to, ]);
		}

	public static function getRideCategoriesForMember($memberId) : array
		{
		$sql = 'select categoryId from memberCategory where memberId=?';
		$cats = \PHPFUI\ORM::getDataObjectCursor($sql, [$memberId]);
		$returnValue = [];

		foreach ($cats as $row)
			{
			$returnValue[$row->categoryId] = (int)$row->categoryId;
			}

		return $returnValue;
		}

	/**
	 * @return null|scalar
	 */
	public static function getRideCategoryStringForMember($memberId)
		{
		$sql = 'select group_concat(category) from category c left join memberCategory mc on mc.categoryId = c.categoryId where mc.memberId=?';

		return \PHPFUI\ORM::getValue($sql, [$memberId]);
		}
	}
