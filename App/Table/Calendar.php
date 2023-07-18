<?php

namespace App\Table;

class Calendar extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\Calendar::class;

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Calendar>
	 */
	public function getPending() : \PHPFUI\ORM\RecordCursor
		{
		$condition = new \PHPFUI\ORM\Condition('pending', 1);
		$condition->and(new \PHPFUI\ORM\Condition('eventDate', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\GreaterThanEqual()));
		$this->setWhere($condition);
		$this->setOrderBy('eventDate');

		return $this->getRecordCursor();
		}
	}
