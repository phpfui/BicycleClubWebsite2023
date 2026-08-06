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
		$this->addJoin('ride');
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
		$memberTable = new \App\Table\Member();
		$memberTable->setSelect('*');
		$memberTable->addSelect('rideSignup.rideComments');
		$memberTable->addJoin('rideSignup');
		$condition = new \PHPFUI\ORM\Condition('rideId', $ride->rideId);
		$condition->and('status', \App\Enum\RideSignup\Status::CANCELLED->value, new \PHPFUI\ORM\Operator\NotEqual());
		$memberTable->setWhere($condition);

		if ($sortByStatus)
			{
			$memberTable->setOrderBy('status');
			}
		else
			{
			$memberTable->setOrderBy('lastName');
			}
		$memberTable->addOrderBy('firstName');

		return $memberTable->getDataObjectCursor();
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
		$memberTable = new \App\Table\Member();
		$memberTable->setSelect('member.*');
		$memberTable->addSelect('ride.memberId', 'leaderId');
		$memberTable->addSelect('ride.title');
		$memberTable->addSelect('rideSignup.*');

		$memberTable->addJoin('rideSignup');
		$memberTable->addJoin('ride', new \PHPFUI\ORM\Condition('ride.rideId', new \PHPFUI\ORM\Literal('rideSignup.rideId')));

		$condition = new \PHPFUI\ORM\Condition('ride.rideId', $ride->rideId);
		$statuses = [
			\App\Enum\RideSignup\Status::DEFINITELY_RIDING->value,
			\App\Enum\RideSignup\Status::PROBABLY_RIDING->value,
			\App\Enum\RideSignup\Status::WAIT_LIST->value,
			\App\Enum\RideSignup\Status::DEFINITELY_NOT_RIDING->value,
		];
		$condition->and('rideSignup.status', $statuses, new \PHPFUI\ORM\Operator\In());
		$memberTable->setWhere($condition);

		return $memberTable->getDataObjectCursor();
		}

	public function getEarliestRiderSignupTime(\App\Record\Member $rider, string $date) : string
		{
		$sql = 'select signedUpTime
			from rideSignup
			where memberId=? and rideId in (select rideId from ride where rideDate=?)
		order by signedUpTime asc
			limit 1';
		$input = [$rider->memberId, $date];

		return \PHPFUI\ORM::getValue($sql, $input);
		}

	public function getMemberRidesForDate(\App\Record\Member $member, string $date) : \PHPFUI\ORM\DataObjectCursor
		{
		$this->setJoin('ride');
		$this->setGroupBy('rideSignup.memberId');
		$condition = new \PHPFUI\ORM\Condition('rideSignup.memberId', $member->memberId);
		$condition->and('rideDate', $date);
		$this->setWhere($condition);
		$this->setOrderBy('rideSignup.status', 'desc');
		$this->setOrderBy('startTime', 'desc');

		return $this->getDataObjectCursor();
		}

	/**
	 * @return array<string,string>
	 */
	public static function getNewest(\App\Record\Member $member) : array
		{
		$sql = 'select *
			from rideSignup rs
			left join ride r on r.rideId=rs.rideId
			where rs.memberId=? and r.rideId is not null and r.rideDate > 0
			order by r.rideDate desc limit 1';

		return \PHPFUI\ORM::getRow($sql, [$member->memberId]);
		}

	/**
	 * @return array<string,string>
	 */
	public static function getOldest(\App\Record\Member $member) : array
		{
		$sql = 'select *
			from rideSignup rs
			left join ride r on r.rideId=rs.rideId
			where rs.memberId=? and r.rideId is not null and r.rideDate > 0
			order by r.rideDate limit 1';

		return \PHPFUI\ORM::getRow($sql, [$member->memberId]);
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\RideSignup>
	 */
	public function getRidersForAttended(\App\Record\Ride $ride, \App\Enum\RideSignup\Attended $attended = \App\Enum\RideSignup\Attended::CONFIRMED) : \PHPFUI\ORM\RecordCursor
		{
		$this->setOrderBy('signedUpTime');
		$condition = new \PHPFUI\ORM\Condition('rideId', $ride->rideId);
		$condition->and('attended', $attended->value);
		$this->setWhere($condition);

		return $this->getRecordCursor();
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\RideSignup>
	 */
	public function getRidersForStatus(\App\Record\Ride $ride, \App\Enum\RideSignup\Status $status) : \PHPFUI\ORM\RecordCursor
		{
		$this->setOrderBy('signedUpTime');
		$condition = new \PHPFUI\ORM\Condition('rideId', $ride->rideId);
		$condition->and('status', $status->value);
		$this->setWhere($condition);

		return $this->getRecordCursor();
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
			\App\Enum\RideSignup\Status::WAIT_LIST->value => 'Wait List',
			\App\Enum\RideSignup\Status::DEFINITELY_NOT_RIDING->value => "Can't Ride Because",
			\App\Enum\RideSignup\Status::CANCELLED->value => 'Cancelled',
		];
		}

	public function getRidesForMember(\App\Record\Member $member, string $startDate = '2000-01-01', string $endDate = '2999-12-31') : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select *
			from rideSignup rs
			left join ride r on r.rideId=rs.rideId
			where rs.memberId=? and r.rideDate>=? and r.rideDate<=?
			order by r.rideDate desc, rs.memberId';
		$this->setSelect('rideSignup.*');
		$this->addSelect('ride.*');
		$this->setJoin('ride');
		$condition = new \PHPFUI\ORM\Condition('rideSignup.memberId', $member->memberId);
		$condition->and('rideDate', $endDate, new \PHPFUI\ORM\Operator\LessThanEqual());
		$condition->and('rideDate', $startDate, new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$this->setWhere($condition);
		$this->setOrderBy('rideDate', 'desc');
		$this->setOrderBy('ride.rideId');

		return $this->getDataObjectCursor();
		}

	public static function getSignedUpByPermmission(\App\Record\Ride $ride, \App\Record\Permission $permission) : \PHPFUI\ORM\DataObjectCursor
		{
		$memberTable = new \App\Table\Member();
		$memberTable->setSelect('member.*');
		$memberTable->addSelect('userPermission.*');
		$memberTable->addSelect('rideSignup.*');
		$memberTable->setJoin('userPermission');
		$memberTable->addJoin('rideSignup');
		$condition = new \PHPFUI\ORM\Condition('rideId', $ride->rideId);
		$condition->and('permissionGroup', $permission->permissionId);
		$memberTable->setWhere($condition);

		return $memberTable->getDataObjectCursor();
		}

	public function getSignedUpRiders(int $rideId, string $order = 'signedUpTime') : \PHPFUI\ORM\DataObjectCursor
		{
		$this->setJoin('member');
		$this->setOrderBy($order);
		$condition = new \PHPFUI\ORM\Condition('status', \App\Enum\RideSignup\Status::WAIT_LIST->value, new \PHPFUI\ORM\Operator\LessThanEqual());
		$condition->and('rideId', $rideId);
		$this->setWhere($condition);

		return $this->getDataObjectCursor();
		}

	public function moveWaitListToRideFromRide(\App\Record\Ride $ride, \App\Record\Ride $clonedRide) : void
		{
		$condition = new \PHPFUI\ORM\Condition('rideId', $clonedRide->rideId);
		$condition->and('status', \App\Enum\RideSignup\Status::WAIT_LIST->value);
		$this->setWhere($condition);

		foreach ($this->getRecordCursor() as $rideSignup)
			{
			$rideSignup->delete();
			$rideSignup->ride = $ride;
			$rideSignup->insert();
			}
		}
	}
