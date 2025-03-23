<?php

namespace App\Table;

class DiscountCode extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\DiscountCode::class;

	public function getActiveCodes() : \PHPFUI\ORM\RecordCursor
		{
		$condition = new \PHPFUI\ORM\Condition('startDate', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\LessThanEqual());
		$condition->and('expirationDate', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$validNumberCondition = new \PHPFUI\ORM\Condition('validItemNumbers', '1-1');
		$validNumberCondition->or('validItemNumbers', '1-2');
		$validNumberCondition->or('validItemNumbers', '1');
		$condition->andNot($validNumberCondition);
		$this->setWhere($condition);

		return $this->getRecordCursor();
		}

	public function getActiveMembershipCodes() : \PHPFUI\ORM\RecordCursor
		{
		$condition = new \PHPFUI\ORM\Condition('startDate', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\LessThanEqual());
		$condition->and('expirationDate', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$validNumberCondition = new \PHPFUI\ORM\Condition('validItemNumbers', '1-1');
		$validNumberCondition->or('validItemNumbers', '1-2');
		$validNumberCondition->or('validItemNumbers', '1');
		$condition->and($validNumberCondition);
		$this->setWhere($condition);

		return $this->getRecordCursor();
		}

	public function getAllCodes() : \PHPFUI\ORM\ArrayCursor
		{
		$sql = 'select d.*,count(i.discountCodeId) used from discountCode d left join invoice i on i.discountCodeId=d.discountCodeId group by discountCodeId order by d.discountCode';

		return \PHPFUI\ORM::getArrayCursor($sql);
		}
	}
