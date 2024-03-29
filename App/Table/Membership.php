<?php

namespace App\Table;

class Membership extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\Membership::class;

	/**
	 * @return null|scalar
	 */
	public static function currentMembershipCount()
		{
		$sql = 'SELECT count(*) FROM membership where expires>=?';

		return \PHPFUI\ORM::getValue($sql, [\App\Tools\Date::todayString()]);
		}

	/**
	 * @return null|scalar
	 */
	public static function currentSubscriptionCount()
		{
		$sql = 'SELECT count(*) FROM membership where renews>=?';

		return \PHPFUI\ORM::getValue($sql, [\App\Tools\Date::todayString()]);
		}

	public function getExpiringMemberships(string $start, string $end) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select * from membership s left join member m on m.membershipId=s.membershipId
				where s.expires>=? and s.expires<=? and s.joined>"1000-01-01"';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$start, $end]);
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Membership>
	 */
	public function getMemberlessMemberships(string $date) : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'select * from membership where joined<? and membershipId not in (select membershipId from member)';

		return \PHPFUI\ORM::getRecordCursor($this->instance, $sql, [$date]);
		}

	public static function getMembershipsLastNames(int $membershipId) : string
		{
		$lastNames = [];

		$sql = 'select distinct member.lastName from member where member.membershipId=?';

		return \implode('/', \PHPFUI\ORM::getValueArray($sql, [$membershipId]));
		}

	public function getNewMemberships(int $daysBack = 1) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select * from membership s left join member m on m.membershipId=s.membershipId
				where s.joined=? and s.expires>?';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [\App\Tools\Date::todayString(-$daysBack), \App\Tools\Date::todayString()]);
		}

	public function getOldestMembership() : \App\Record\Membership
		{
		$sql = 'select * from membership where expires>=? and joined>"1000-01-01" order by joined limit 1';

		$membership = new \App\Record\Membership();
		$membership->loadFromSQL($sql, [\App\Tools\Date::todayString()]);

		return $membership;
		}

	public function getRenewedMemberships(int $daysBack) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select * from membership s left join member m on m.membershipId=s.membershipId
				where s.lastRenewed=?';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [\App\Tools\Date::todayString(-$daysBack)]);
		}

	public function getRenewingMemberships(string $date) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select * from membership s left join member m on m.membershipId=s.membershipId
				where s.renews=?';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$date]);
		}
	}
