<?php

namespace App\Table;

class Newsletter extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\Newsletter::class;

	public function getAllByYear(int $year) : \PHPFUI\ORM\RecordCursor
		{
		$start = \App\Tools\Date::toString(\gregoriantojd(1, 1, $year));
		$end = \App\Tools\Date::toString(\gregoriantojd(12, 31, $year));
		$sql = 'select * from newsletter where date >= ? and date <= ? order by date';

		return \PHPFUI\ORM::getRecordCursor($this->instance, $sql, [$start, $end, ]);
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
