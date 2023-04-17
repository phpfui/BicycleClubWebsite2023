<?php

namespace App\Table;

class Poll extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\Poll::class;

	public static function byYear(int $year) : \PHPFUI\ORM\RecordCursor
		{
		$pollTable = new \App\Table\Poll();
		$condition = new \PHPFUI\ORM\Condition('startDate', \App\Tools\Date::makeString($year, 1, 1), new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and(new \PHPFUI\ORM\Condition('startDate', \App\Tools\Date::makeString($year, 12, 31), new \PHPFUI\ORM\Operator\LessThanEqual()));
		$pollTable->setWhere($condition);
		$pollTable->setOrderBy('startDate');

		return $pollTable->getRecordCursor();
		}

	public static function current() : \PHPFUI\ORM\RecordCursor
		{
		$pollTable = new \App\Table\Poll();
		$today = \App\Tools\Date::todayString();
		$condition = new \PHPFUI\ORM\Condition('startDate', $today, new \PHPFUI\ORM\Operator\LessThanEqual());
		$condition->and(new \PHPFUI\ORM\Condition('endDate', $today, new \PHPFUI\ORM\Operator\GreaterThanEqual()));
		$pollTable->setWhere($condition);
		$pollTable->setOrderBy('startDate');

		return $pollTable->getRecordCursor();
		}

	public static function future() : \PHPFUI\ORM\RecordCursor
		{
		$pollTable = new \App\Table\Poll();
		$today = \App\Tools\Date::todayString();
		$condition = new \PHPFUI\ORM\Condition('startDate', $today, new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$pollTable->setWhere($condition);
		$pollTable->setOrderBy('startDate');

		return $pollTable->getRecordCursor();
		}

	public function getRequiredPolls() : \PHPFUI\ORM\RecordCursor
		{
		$pollTable = new \App\Table\Poll();
		$today = \App\Tools\Date::todayString();
		$condition = new \PHPFUI\ORM\Condition('startDate', $today, new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and(new \PHPFUI\ORM\Condition('endDate', $today, new \PHPFUI\ORM\Operator\LessThanEqual()));
		$condition->and(new \PHPFUI\ORM\Condition('required', 0, new \PHPFUI\ORM\Operator\GreaterThan()));
		$pollTable->setWhere($condition);
		$pollTable->setOrderBy('startDate');

		return $pollTable->getRecordCursor();
		}

	public static function latest() : \App\Record\Poll
		{
		$sql = 'select * from poll where startDate<=? order by startDate desc limit 1';
		$input = [\App\Tools\Date::todayString()];

		$record = new \App\Record\Poll();
		$record->loadFromSQL($sql, $input);

		return $record;
		}

	public static function myPolls() : \PHPFUI\ORM\RecordCursor
		{
		$pollTable = new \App\Table\Poll();
		$condition = new \PHPFUI\ORM\Condition('memberId', \App\Model\Session::signedInMemberId());
		$pollTable->setWhere($condition);
		$pollTable->setOrderBy('startDate');

		return $pollTable->getRecordCursor();
		}

	public static function oldest() : \App\Record\Poll
		{
		$sql = 'select * from poll order by startDate limit 1';

		$record = new \App\Record\Poll();
		$record->loadFromSQL($sql);

		return $record;
		}
	}
