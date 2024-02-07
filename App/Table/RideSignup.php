<?php

namespace App\Table;

class RideSignup extends \PHPFUI\ORM\Table
{
	final public const CANCELLED = 6;

	final public const CONFIRMED = 2;

	final public const DEFINITELY_NOT_RIDING = 5;

	final public const DEFINITELY_RIDING = 1;

	final public const NO_SHOW = 1;

	final public const POSSIBLY_RIDING = 3;

	final public const PROBABLY_RIDING = 2;

	final public const REMOVE = 0;

	final public const UNKNOWN = 0;

	final public const WAIT_LIST = 4;

	protected static string $className = '\\' . \App\Record\RideSignup::class;

	public function deleteOtherSignedUpRides(\App\Record\Ride $ride, \App\Record\Member $member) : static
		{
		$sql = 'delete from rideSignup where rideId in (select rideId from ride where rideDate=(select rideDate from ride where rideId=:rideId) and rideId!=:rideId) and memberId=:memberId and status<:status';
		$input = ['rideId' => $ride->rideId, 'memberId' => $member->memberId, 'status' => self::DEFINITELY_NOT_RIDING];
		\PHPFUI\ORM::execute($sql, $input);

		return $this;
		}

	/**
	 * @param array<string,array<int>|string> $parameters
	 */
	public function find(array $parameters) : \PHPFUI\ORM\DataObjectCursor
		{
		$paceJoin = new \PHPFUI\ORM\Condition('pace.paceId', new \PHPFUI\ORM\Literal('ride.paceId'));
		$this->addJoin('pace', $paceJoin);

		$condition = $this->getWhereCondition();

		if (! empty($parameters['start']))
			{
			$condition->and('ride.rideDate', $parameters['start'], new \PHPFUI\ORM\Operator\GreaterThanEqual());
			}

		if (! empty($parameters['end']))
			{
			$condition->and('ride.rideDate', $parameters['end'], new \PHPFUI\ORM\Operator\LessThanEqual());
			}

		if (! empty($parameters['minDistance']))
			{
			$condition->and('ride.mileage', $parameters['minDistance'], new \PHPFUI\ORM\Operator\GreaterThanEqual());
			}

		if (! empty($parameters['maxDistance']))
			{
			$condition->and('ride.mileage', $parameters['maxDistance'], new \PHPFUI\ORM\Operator\LessThanEqual());
			}

		if ($parameters['startLocationId'] ?? 0)
			{
			$condition->and('ride.startLocationId', $parameters['startLocationId']);
			}

		if (! empty($parameters['title']))
			{
			$condition->and('ride.title', '%' . $parameters['title'] . '%', new \PHPFUI\ORM\Operator\Like());
			}

		if (! empty($parameters['description']))
			{
			$condition->and('ride.description', '%' . $parameters['description'] . '%', new \PHPFUI\ORM\Operator\Like());
			}

		if (! empty($parameters['categories']))
			{
			$paceTable = new \App\Table\Pace();
			$paces = $paceTable->getPacesForCategories($parameters['categories']);

			if (\count($paces))
				{
				$condition->and('ride.paceId', $paces, new \PHPFUI\ORM\Operator\In());
				}
			}
		$this->setWhere($condition);

		return $this->getDataObjectCursor();
		}

	public function getAllSignedUpRiders(\App\Record\Ride $ride, bool $sortByStatus = true) : \PHPFUI\ORM\DataObjectCursor
		{
		$sortByStatus = $sortByStatus ? 'r.status asc,' : '';
		$sql = "select * from member m left join rideSignup r on r.memberId=m.memberId where r.rideId=? and r.status!=? order by {$sortByStatus} m.lastName, m.firstName";

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$ride->rideId, self::CANCELLED]);
		}

	/**
	 * @return array<string>
	 */
	public static function getAttendedStatus() : array
		{
		return [
			'Unknown',
			'No Show',
			'Confirmed',
		];
		}

	public function getCommittedRiders(\App\Record\Ride $ride) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select m.*,r.memberId as leaderId,r.title,rs.* from member m left join rideSignup rs on rs.memberId=m.memberId left join ride r on r.rideId=rs.rideId where r.rideId=? and rs.status in (1,2,3,4)';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$ride->rideId]);
		}

	public function getEarliestRiderSignupTime(\App\Record\Member $rider, string $date) : string
		{
		$sql = 'select signedUpTime from rideSignup where memberId=? and rideId in (select rideId from ride where rideDate=?) order by signedUpTime asc limit 1';
		$input = [$rider->memberId, $date];

		return \PHPFUI\ORM::getValue($sql, $input);
		}

	public function getMemberRidesForDate(\App\Record\Member $member, string $date) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select * from rideSignup rs left join ride r on r.rideId=rs.rideId where rs.memberId=? and r.rideDate=? order by rs.status desc, r.startTime desc';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$member->memberId, $date]);
		}

	/**
	 * @return array<string,string>
	 */
	public static function getNewest(\App\Record\Member $member) : array
		{
		$sql = 'select * from rideSignup rs left join ride r on r.rideId=rs.rideId where rs.memberId=? and r.rideId is not null and r.rideDate > 0 order by r.rideDate desc limit 1';

		return \PHPFUI\ORM::getRow($sql, [$member->memberId]);
		}

	/**
	 * @return array<string,string>
	 */
	public static function getOldest(\App\Record\Member $member) : array
		{
		$sql = 'select * from rideSignup rs left join ride r on r.rideId=rs.rideId where rs.memberId=? and r.rideId is not null and r.rideDate > 0 order by r.rideDate limit 1';

		return \PHPFUI\ORM::getRow($sql, [$member->memberId]);
		}

	public function getRiderFrequency(int $status, int $startDate, int $endDate) : \PHPFUI\ORM\ArrayCursor
		{
		$sql = 'select count(*) count,rs.memberId from ridesignup rs left join ride r on r.rideId=rs.rideId where rs.attended=? and r.rideDate>=? and r.rideDate<=? group by r.memberid order by count desc';

		return \PHPFUI\ORM::getArrayCursor($sql, [$status, $startDate, $endDate]);
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\RideSignup>
	 */
	public function getRidersForAttended(\App\Record\Ride $ride, int $attended = self::CONFIRMED) : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'select * from rideSignup where rideId=? and attended=? order by signedUpTime';

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\RideSignup(), $sql, [$ride->rideId, $attended]);
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\RideSignup>
	 */
	public function getRidersForStatus(\App\Record\Ride $ride, int $status) : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'select * from rideSignup where rideId=? and status=? order by signedUpTime';

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\RideSignup(), $sql, [$ride->rideId, $status]);
		}

	/**
	 * @return array<string>
	 */
	public static function getRiderStatus() : array
		{
		return [
			'Remove From Ride',
			'Definitely Riding',
			'Probably Riding',
			'Possibly',
			'Wait List',
			"Can't Ride Because",
			'Cancelled',
		];
		}

	public function getRidesForMember(\App\Record\Member $member, string $startDate = '2000-01-01', string $endDate = '2999-12-31') : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select * from rideSignup rs left join ride r on r.rideId=rs.rideId where rs.memberId=? and r.rideDate>=? and r.rideDate<=? order by r.rideDate desc';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$member->memberId, $startDate, $endDate]);
		}

	public static function getSignedUpByPermmission(\App\Record\Ride $ride, \App\Record\Permission $permission) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'SELECT * FROM member m left join userPermission u on u.memberId = m.memberId left join rideSignup r on r.memberId=m.memberId WHERE u.permissionGroup = ? and r.rideId = ?';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$permission->permissionId, $ride->rideId]);
		}

	public function getSignedUpRiders(int $rideId, string $order = 'r.signedUpTime') : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select * from member m left join rideSignup r on r.memberId=m.memberId where r.rideId=? and r.status<=? order by ' . $order;

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$rideId, self::WAIT_LIST]);
		}

	public function moveWaitListToRideFromRide(\App\Record\Ride $ride, \App\Record\Ride $clonedRide) : void
		{
		$sql = 'select * from rideSignup where rideId=? and status=?';
		$input = [$clonedRide->rideId, self::WAIT_LIST];

		$waitlist = \PHPFUI\ORM::getRecordCursor(new \App\Record\RideSignup(), $sql, $input);

		foreach ($waitlist as $rideSignup)
			{
			$rideSignup->delete();
			$rideSignup->ride = $ride;
			$rideSignup->insert();
			}
		}
	}
