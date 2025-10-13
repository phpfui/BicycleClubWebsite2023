<?php

namespace App\Table;

class PublicPage extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\PublicPage::class;

	public function setDates() : static
		{
		$condition = $this->getWhereCondition();
		$startCondition = new \PHPFUI\ORM\Condition('startDate', null, new \PHPFUI\ORM\Operator\IsNull());
		$startCondition->or('startDate', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\LessThanEqual());
		$endCondition = new \PHPFUI\ORM\Condition('endDate', null, new \PHPFUI\ORM\Operator\IsNull());
		$endCondition->or('endDate', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and($startCondition)->and($endCondition);

		return $this;
		}
	}
