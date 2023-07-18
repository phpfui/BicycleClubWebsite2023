<?php

namespace App\Table;

class VolunteerPoint extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\VolunteerPoint::class;

	public static function getForMemberDate(int $memberId, string $startDate, string $endDate) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select vp.*,je.name from volunteerPoint vp
			left join jobEvent je on je.jobEventId=vp.jobEventId
			where vp.memberId=? and vp.date>=? and vp.date<=? order by vp.date';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$memberId, $startDate, $endDate]);
		}
	}
