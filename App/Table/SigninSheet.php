<?php

namespace App\Table;

class SigninSheet extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\SigninSheet::class;

	public static function fromMember(int $memberId) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = self::getSelectedFields() . ' where s.memberId=? order by s.dateAdded desc';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$memberId]);
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\SigninSheet>
	 */
	public static function getApprovedUnawarded(string $startDate, string $endDate = '') : \PHPFUI\ORM\RecordCursor
		{
		if (! $endDate)
			{
			$endDate = \App\Tools\Date::todayString();
			}
		$sql = 'select * from signinSheet where dateAdded>=? and dateAdded<=? and pending=0 and pointsAwarded=0';

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\SigninSheet(), $sql, [$startDate, $endDate]);
		}

	public static function getForDateRange(string $startDate, string $endDate) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select distinct s.memberId,r.rideId from signinSheet s left join signinSheetRide r on r.signinSheetId=s.signinSheetId where s.dateAdded>=? and s.dateAdded<=?';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$startDate, $endDate]);
		}

	public static function getForMemberDate(int $memberId, string $startDate, string $endDate) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select * from signinSheet where pending!=0 and memberId=? and dateAdded>=? and dateAdded<=? order by dateAdded desc';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$memberId, $startDate, $endDate]);
		}

	/**
	 * @param array<string,string> $parameters
	 */
	public function search(array $parameters) : bool
		{
		$condition = $this->getWhereCondition();
		$this->addJoin('signinSheetRide', 'signinSheetId');
		$this->addJoin('ride', new \PHPFUI\ORM\Condition('ride.rideId', new \PHPFUI\ORM\Field('signinSheetRide.rideId')));
		$returnValue = false;

		if (! empty($parameters['MemberName']))
			{
			$condition->and('signinSheet.memberId', $parameters['MemberName']);
			$returnValue = true;
			}

		if (! empty($parameters['ride_title']))
			{
			$condition->and('ride.title', '%' . $parameters['ride_title'] . '%', new \PHPFUI\ORM\Operator\Like());
			$returnValue = true;
			}

		if (! empty($parameters['addedEnd']))
			{
			$condition->and('signinSheet.dateAdded', $parameters['addedEnd'], new \PHPFUI\ORM\Operator\LessThanEqual());
			$returnValue = true;
			}

		if (! empty($parameters['addedStart']))
			{
			$condition->and('signinSheet.dateAdded', $parameters['addedStart'], new \PHPFUI\ORM\Operator\GreaterThanEqual());
			$returnValue = true;
			}

		if (! empty($parameters['rideDateEnd']))
			{
			$condition->and('ride.rideDate', $parameters['rideDateEnd'], new \PHPFUI\ORM\Operator\LessThanEqual());
			$returnValue = true;
			}

		if (! empty($parameters['rideDateStart']))
			{
			$condition->and('ride.rideDate', $parameters['rideDateStart'], new \PHPFUI\ORM\Operator\GreaterThanEqual());
			$returnValue = true;
			}

		return $returnValue;
		}

	private static function getSelectedFields() : string
		{
		return 'select s.*,r.* from signinSheet s
				left outer join signinSheetRide sr on sr.signinSheetId=s.signinSheetId
				left outer join ride r on r.rideId=sr.rideId ';
		}
	}
