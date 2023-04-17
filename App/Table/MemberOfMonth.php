<?php

namespace App\Table;

class MemberOfMonth extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\MemberOfMonth::class;

	public function current() : \App\Record\MemberOfMonth
		{
		$memberOfMonth = new \App\Record\MemberOfMonth();
		$memberOfMonth->loadFromSQL(
			'select * from memberOfMonth mom left join member m on m.memberId=mom.memberId where mom.month<=? order by mom.month desc limit 1',
			[\App\Tools\Date::todayString()]
		);

		return $memberOfMonth;
		}

	public function getFirst() : \App\Record\MemberOfMonth
		{
		$memberOfMonth = new \App\Record\MemberOfMonth();
		$memberOfMonth->loadFromSQL('select * from memberOfMonth order by month limit 1');

		return $memberOfMonth;
		}

	public function getLatest() : \App\Record\MemberOfMonth
		{
		$memberOfMonth = new \App\Record\MemberOfMonth();
		$memberOfMonth->loadFromSQL('select * from memberOfMonth mom left join member m on m.memberId=mom.memberId order by mom.month desc limit 1');

		return $memberOfMonth;
		}

	public function getRange(string $first, string $last) : \PHPFUI\ORM\ArrayCursor
		{
		$sql = 'select mom.*,m.* from memberOfMonth mom left join member m on m.memberId=mom.memberId where mom.month>=? and mom.month<=? order by mom.month';

		return \PHPFUI\ORM::getArrayCursor($sql, [$first, $last]);
		}
	}
