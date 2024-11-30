<?php

namespace App\Table;

class RideSignup extends \PHPFUI\ORM\Table
{
	protected static string $className = '\\' . \App\Record\RideSignup::class;

	public function deleteOtherSignedUpRides(\App\Record\Ride $ride, \App\Record\Member $member) : static
		{
		$sql = 'delete from rideSignup where rideId in (select rideId from ride where rideDate=(select rideDate from ride where rideId=:rideId) and rideId!=:rideId) and memberId=:memberId and status<:status';
		$input = ['rideId' => $ride->rideId, 'memberId' => $member->memberId, 'status' => \App\Enum\RideSignup\Status::DEFINITELY_NOT_RIDING->value];
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

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$ride->rideId, \App\Enum\RideSignup\Status::CANCELLED->value]);
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
	public function getRidersForAttended(\App\Record\Ride $ride, \App\Enum\RideSignup\Attended $attended = \App\Enum\RideSignup\Attended::CONFIRMED) : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'select * from rideSignup where rideId=? and attended=? order by signedUpTime';

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\RideSignup(), $sql, [$ride->rideId, $attended]);
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\RideSignup>
	 */
	public function getRidersForStatus(\App\Record\Ride $ride, \App\Enum\RideSignup\Status $status) : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'select * from rideSignup where rideId=? and status=? order by signedUpTime';

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\RideSignup(), $sql, [$ride->rideId, $status->value]);
		}

	/**
	 * @return array<string>
	 */
	public static function getRiderStatus() : array
		{
		return [
			\App\Enum\RideSignup\Status::REMOVE->value => 'Remove From Ride',
			\App\Enum\RideSignup\Status::DEFINITELY_RIDING->value => 'Definitely Riding',
			\App\Enum\RideSignup\Status::PROBABLY_RIDING->value => 'Probably Riding',
			//			\App\Enum\RideSignup\Status::POSSIBLY_RIDING->value => 'Possibly',
			\App\Enum\RideSignup\Status::WAIT_LIST->value => 'Wait List',
			\App\Enum\RideSignup\Status::DEFINITELY_NOT_RIDING->value => "Can't Ride Because",
			\App\Enum\RideSignup\Status::CANCELLED->value => 'Cancelled',
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

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$rideId, \App\Enum\RideSignup\Status::WAIT_LIST->value]);
		}

	public function moveWaitListToRideFromRide(\App\Record\Ride $ride, \App\Record\Ride $clonedRide) : void
		{
		$sql = 'select * from rideSignup where rideId=? and status=?';
		$input = [$clonedRide->rideId, \App\Enum\RideSignup\Status::WAIT_LIST->value];

		$waitlist = \PHPFUI\ORM::getRecordCursor(new \App\Record\RideSignup(), $sql, $input);

		foreach ($waitlist as $rideSignup)
			{
			$rideSignup->delete();
			$rideSignup->ride = $ride;
			$rideSignup->insert();
			}
		}
	}
