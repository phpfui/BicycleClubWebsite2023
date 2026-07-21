<?php

namespace App\Table;

class VolunteerPoint extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\VolunteerPoint::class;

	public function getForMemberDate(int $memberId, string $startDate, string $endDate) : \PHPFUI\ORM\DataObjectCursor
		{
		$this->setSelect('volunteerPoint.*');
		$this->addSelect('jobEvent.name');
		$condition = new \PHPFUI\ORM\Condition('volunteerPoint.memberId', $memberId);
		$condition->and('volunteerPoint.date', $startDate, new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('volunteerPoint.date', $endDate, new \PHPFUI\ORM\Operator\LessThanEqual());
		$this->setWhere($condition);
		$this->setJoin('jobEvent');
		$this->setOrderBy('volunteerPoint.date');

		return $this->getDataObjectCursor();
		}
	}
