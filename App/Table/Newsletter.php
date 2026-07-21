<?php

namespace App\Table;

class Newsletter extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\Newsletter::class;

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Newsletter>
	 */
	public function getAllByYear(int $year) : \PHPFUI\ORM\RecordCursor
		{
		$this->setLimit(0);
		$this->setOrderBy('date');
		$start = \App\Tools\Date::toString(\gregoriantojd(1, 1, $year));
		$end = \App\Tools\Date::toString(\gregoriantojd(12, 31, $year));

		$condition = new \PHPFUI\ORM\Condition('date', $start, new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('date', $end, new \PHPFUI\ORM\Operator\LessThanEqual());
		$this->setWhere($condition);

		return $this->getRecordCursor();
		}

	public function getFirst(string $ascending = '') : \App\Record\Newsletter
		{
		$this->setLimit(1);
		$this->setOrderBy('date', $ascending);

		$cursor = $this->getRecordCursor();

		if (\count($cursor))
			{
			return $cursor->current();
			}

		return new \App\Record\Newsletter();
		}

	public function getLatest() : \App\Record\Newsletter
		{
		return $this->getFirst('desc');
		}
	}
