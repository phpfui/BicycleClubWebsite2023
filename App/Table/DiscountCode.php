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

	public function getActiveMembershipCodes(int $membershipType) : \PHPFUI\ORM\RecordCursor
		{
		$condition = new \PHPFUI\ORM\Condition('startDate', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\LessThanEqual());
		$condition->and('expirationDate', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$validNumberCondition = new \PHPFUI\ORM\Condition('validItemNumbers', $membershipType . '-1');
		$validNumberCondition->or('validItemNumbers', $membershipType . '-2');
		$validNumberCondition->or('validItemNumbers', $membershipType);
		$condition->and($validNumberCondition);
		$this->setWhere($condition);

		return $this->getRecordCursor();
		}
	}
