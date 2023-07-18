<?php

namespace App\Table;

class SigninSheetRide extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\SigninSheetRide::class;

	public static function rides(int $signinSheetId) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select * from signinSheetRide sr left join ride r on sr.rideId=r.rideId where sr.signinSheetId=? order by r.rideDate desc';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$signinSheetId]);
		}
	}
