<?php

namespace App\Table;

class AssistantLeader extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\AssistantLeader::class;

	public static function getForDateRange(string $startDate, string $endDate) : iterable
		{
		$sql = 'select al.* from assistantLeader al left join ride r on r.rideId=al.rideId where r.rideDate>=? and r.rideDate<=?';

		return \PHPFUI\ORM::getArrayCursor($sql, [$startDate, $endDate]);
		}

	public static function getForMemberDate(int $memberId, string $startDate, string $endDate) : iterable
		{
		$sql = 'select r.* from ride r left join assistantLeader a on a.rideId=r.rideId where a.memberId=? and r.rideDate>=? and r.rideDate<=? order by r.rideDate';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$memberId, $startDate, $endDate]);
		}

	/**
	 * Return RecordCursor<Member>
	 */
	public static function getForRide(\App\Record\Ride $ride) : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'select * from assistantLeader al left join member m on m.memberId=al.memberId where al.rideId=?';

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\Member(), $sql, [$ride->rideId]);
		}
	}
