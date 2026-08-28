<?php

namespace App\Table;

class Ride extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\Ride::class;

	public static function changePace(int $from, int $to) : bool
		{
		$sql = 'update ride set paceId=:to where paceId=:from';

		return \PHPFUI\ORM::execute($sql, ['from' => $from,
			'to' => $to, ]);
		}

	public function distanceToRide(float $latitude, float $longitude, string $startDate, string $endDate) : static
		{
		$this->setSelect('ride.rideId');
		$this->addSelect('ride.rideDate');
		$this->addSelect('ride.title', 'name');
		$this->addSelect('firstName');
		$this->addSelect('lastName');
		$this->addSelect('RWGPS.RWGPSId');
		$this->addSelect('RWGPS.title');
		$this->addSelect('RWGPS.latitude');
		$this->addSelect('RWGPS.longitude');
		$formula = "(6371000 * ACOS(COS(RADIANS({$latitude})) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS({$longitude})) + SIN(RADIANS({$latitude})) * SIN(RADIANS(latitude))))";
		$this->addSelect(new \PHPFUI\ORM\Literal($formula), 'meters');
		$this->setJoin('rideRWGPS');
		$this->addJoin('RWGPS', new \PHPFUI\ORM\Condition('RWGPS.RWGPSId', new \PHPFUI\ORM\Field('rideRWGPS.RWGPSId')));
		$this->addJoin('member');
		$condition = new \PHPFUI\ORM\Condition('RWGPS.RWGPSId', null, new \PHPFUI\ORM\Operator\NotEqual());

		if (\strlen($startDate))
			{
			$condition->and('rideDate', $startDate, new \PHPFUI\ORM\Operator\GreaterThanEqual());
			}

		if (\strlen($endDate))
			{
			$condition->and('rideDate', $endDate, new \PHPFUI\ORM\Operator\LessThanEqual());
			}

		$this->setWhere($condition);

		return $this;
		}

	/**
	 * @param array<string,array<int>|string> $parameters
	 */
	public function find(array $parameters) : \PHPFUI\ORM\DataObjectCursor
		{
		$this->setJoin('pace');

		$condition = new \PHPFUI\ORM\Condition();

		if (! empty($parameters['start']))
			{
			$condition->and('rideDate', $parameters['start'], new \PHPFUI\ORM\Operator\GreaterThanEqual());
			}

		if (! empty($parameters['end']))
			{
			$condition->and('rideDate', $parameters['end'], new \PHPFUI\ORM\Operator\LessThanEqual());
			}

		if (! empty($parameters['minDistance']))
			{
			$condition->and('mileage', $parameters['minDistance'], new \PHPFUI\ORM\Operator\GreaterThanEqual());
			}

		if (! empty($parameters['maxDistance']))
			{
			$condition->and('mileage', $parameters['maxDistance'], new \PHPFUI\ORM\Operator\LessThanEqual());
			}

		if ($parameters['startLocationId'] ?? 0)
			{
			$condition->and('startLocationId', $parameters['startLocationId']);
			}

		if (! empty($parameters['title']))
			{
			$condition->and('title', '%' . $parameters['title'] . '%', new \PHPFUI\ORM\Operator\Like());
			}

		if (! empty($parameters['description']))
			{
			$condition->and('description', '%' . $parameters['description'] . '%', new \PHPFUI\ORM\Operator\Like());
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
		$condition->and('pending', 0);
		$this->setWhere($condition);
		$this->addOrderBy('rideDate');
		$this->addOrderBy('pace.ordering');
		$this->addOrderBy('startTime');
		$this->addOrderBy('mileage');

		return $this->getDataObjectCursor();
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public function futureRidesForMember(\App\Record\Member $member) : \PHPFUI\ORM\RecordCursor
		{
		$this->setLimit(0);
		$this->setOrderBy('rideDate');
		$condition = new \PHPFUI\ORM\Condition('rideDate', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('memberId', $member->memberId);
		$this->setWhere($condition);

		return $this->getRecordCursor();
		}

	/**
	 * @return array<int,array<string,int>>
	 */
	public static function getCountByStartLocation() : array
		{
		$sql = 'select startLocationId,count(rideId) as count,rideId
			from ride
			where startLocationId>0
			group by startLocationId';
		$rides = \PHPFUI\ORM::getDataObjectCursor($sql, []); // OK
		$map = [];

		foreach ($rides as $ride)
			{
			$map[$ride['startLocationId']]['count'] = $ride['count'];
			$map[$ride['startLocationId']]['rideId'] = $ride['rideId'];
			}

		return $map;
		}

	public static function getCueSheetRideCount(\App\Record\CueSheet $cueSheet) : int
		{
		$sql = 'select count(*) from ride where cueSheetId=?';

		return (int)\PHPFUI\ORM::getValue($sql, [$cueSheet->cueSheetId]);
		}

	public function getCuesheetStats(int $year) : \PHPFUI\ORM\DataObjectCursor
		{
		$this->setSelect('cueSheet.name', 'cuesheetname');
		$this->addSelect('startLocation.name');
		$this->addSelect('link');
		$this->addSelect('directions');
		$this->addSelect('cueSheet.cueSheetId');
		$this->addSelect('cueSheet.startLocationId');
		$this->addSelect(new \PHPFUI\ORM\Literal('count(ride.cueSheetId)'), 'count');

		$this->setJoin('cueSheet', type:'inner');
		$this->addJoin('startLocation', new \PHPFUI\ORM\Condition('startLocation.startLocationId', new \PHPFUI\ORM\Literal('cueSheet.startLocationId')), 'inner');

		$condition = new \PHPFUI\ORM\Condition('rideDate', \App\Tools\Date::makeString($year, 1, 1), new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('rideDate', \App\Tools\Date::makeString($year, 12, 31), new \PHPFUI\ORM\Operator\LessThanEqual());
		$condition->and('ride.cueSheetId', 0, new \PHPFUI\ORM\Operator\GreaterThan());
		$this->setWhere($condition);
		$this->setGroupBy('cueSheet.cueSheetId');
		$this->setOrderBy('count', 'desc');

		return $this->getDataObjectCursor();
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public function getDateRange(int $start, int $end) : \PHPFUI\ORM\RecordCursor
		{
		$rideTable = new \App\Table\Ride();
		$rideTable->setSelect('ride.*');
		$rideTable->setJoin('pace');
		$rideTable->addOrderBy('rideDate')->addOrderBy('pace.ordering')->addOrderBy('startTime')->addOrderBy('mileage');
		$condition = new \PHPFUI\ORM\Condition('pending', 0);

		if ($start)
			{
			$condition->and('rideDate', \App\Tools\Date::toString($start), new \PHPFUI\ORM\Operator\GreaterThanEqual());
			}

		if ($end)
			{
			$condition->and('rideDate', \App\Tools\Date::toString($end), new \PHPFUI\ORM\Operator\LessThanEqual());
			}
		$rideTable->setWhere($condition);

		return $rideTable->getRecordCursor();
		}

	public static function getFirstRideWithCueSheet() : \App\Record\Ride
		{
		$sql = 'select * from ride where cueSheetId>0 and rideDate>"2000-01-01" and pending=0 order by rideDate limit 1';

		$ride = new \App\Record\Ride();
		$ride->loadFromSQL($sql);

		return $ride;
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public function getForMemberDate(int $memberId, string $startDate = '', string $endDate = '') : \PHPFUI\ORM\RecordCursor
		{
		$this->setLimit(0);
		$this->setOrderBy('rideDate');
		$condition = new \PHPFUI\ORM\Condition('memberId', $memberId);

		if ($startDate)
			{
			$condition->and('rideDate', $startDate, new \PHPFUI\ORM\Operator\GreaterThanEqual());
			}

		if ($endDate)
			{
			$condition->and('rideDate', $endDate, new \PHPFUI\ORM\Operator\LessThanEqual());
			}
		$condition->and('pending', 0);
		$this->setWhere($condition);

		return $this->getRecordCursor();
		}

	public static function getLatestRideWithCueSheet() : \App\Record\Ride
		{
		$sql = 'select * from ride where cueSheetId>0 order by rideDate desc limit 1';

		$ride = new \App\Record\Ride();
		$ride->loadFromSQL($sql);

		return $ride;
		}

	/**
	 * @param array<int> $categories
	 * @param array<int> $leaderTypes of assistantLeaderTypeId's
	 */
	public function getLeadersRides(array $categories, string $startDate, string $endDate, array $leaderTypes = [0]) : \PHPFUI\ORM\DataObjectCursor
		{
		$count = 0;
		$result = $this;

		foreach ($leaderTypes as $leaderType)
			{
			if (++$count > 1)
				{
				$result = new \App\Table\Ride();
				}
			$statusCondition = new \PHPFUI\ORM\Condition('rideStatus', 1, new \PHPFUI\ORM\Operator\GreaterThan());
			$statusCondition->or('unaffiliated', 0);
			$condition = new \PHPFUI\ORM\Condition('rideDate', $startDate, new \PHPFUI\ORM\Operator\GreaterThanEqual());
			$condition->and(new \PHPFUI\ORM\Condition('rideDate', $endDate, new \PHPFUI\ORM\Operator\LessThanEqual()));
			$condition->and(new \PHPFUI\ORM\Condition('ride.memberId', 0, new \PHPFUI\ORM\Operator\GreaterThan()));
			$condition->and('pending', 0);
			$condition->and($statusCondition);

			if (! empty($categories) && ! \in_array(0, $categories))
				{
				$condition->and(new \PHPFUI\ORM\Condition('paceId', $categories, new \PHPFUI\ORM\Operator\In()));
				}
			$result->setSelect('ride.*');

			if ($leaderType)
				{
				$assistantLeaderCondition = new \PHPFUI\ORM\Condition();
				$result->setJoin('assistantLeader');
				$assistantLeaderCondition->or(new \PHPFUI\ORM\Condition('assistantLeader.assistantLeaderTypeId', $leaderType));
				$result->addSelect('assistantLeader.memberId', 'LeaderId');
				$condition->and($assistantLeaderCondition);
				}
			else
				{
				$result->addSelect('ride.memberId', 'LeaderId');
				}
			$result->setWhere($condition);

			if ($count > 1)
				{
				$this->addUnion($result);
				}
			}
		$this->setOrderBy('LeaderId')->addOrderBy('rideDate');

		return $this->getDataObjectCursor();
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public function getMyCategoryRides(\App\Record\Member $member) : \PHPFUI\ORM\RecordCursor
		{
		$this->setLimit(10);
		$this->setSelect('*');
		$this->setJoin('pace');
		$this->setOrderBy('rideDate');
		$this->addOrderBy('pace.ordering');
		$this->addOrderBy('mileage');

		$condition = new \PHPFUI\ORM\Condition('rideDate', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('pending', 0);

		$categories = \App\Table\MemberCategory::getRideCategoriesForMember($member->memberId);

		if (\count($categories))
			{
			$paceTable = new \App\Table\Pace();
			$paceTable->setSelect('paceId');
			$paceTable->setWhere(new \PHPFUI\ORM\Condition('categoryId', $categories, new \PHPFUI\ORM\Operator\In()));
			$condition->and('ride.paceId', $paceTable, new \PHPFUI\ORM\Operator\In());
			}

		$this->setWhere($condition);

		return $this->getRecordCursor();
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public function getMyDateRange(string $start, string $end, \App\Enum\RideSignup\Attended $status) : \PHPFUI\ORM\RecordCursor
		{
		$this->setLimit(0);
		$this->setSelect('ride.*');
		$this->setJoin('pace');
		$this->addJoin('rideSignup');
		$this->addOrderBy('ride.rideDate')->addOrderBy('pace.ordering')->addOrderBy('ride.startTime')->addOrderBy('ride.mileage');
		$condition = new \PHPFUI\ORM\Condition('ride.rideDate', $start, new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('ride.rideDate', $end, new \PHPFUI\ORM\Operator\LessThanEqual());
		$condition->and('ride.pending', 0);
		$condition->and('rideSignup.memberId', \App\Model\Session::getSignedInMemberId());

		if (\App\Enum\RideSignup\Attended::SIGNED_UP != $status)
			{
			$condition->and('rideSignup.attended', $status);
			}
		$this->setWhere($condition);

		return $this->getRecordCursor();
		}

	public static function getMyNewest() : \App\Record\Ride
		{
		$sql = 'select ride.* from ride
			left join rideSignup on rideSignup.rideId=ride.rideId
			where rideSignup.memberId=? and pending=0
			order by ride.rideDate desc limit 1';

		$input = [\App\Model\Session::getSignedInMemberId(), ];

		$ride = new \App\Record\Ride();
		$ride->loadFromSQL($sql, $input);

		return $ride;
		}

	public static function getMyOldest() : \App\Record\Ride
		{
		$sql = 'select ride.* from ride
			left join rideSignup on rideSignup.rideId=ride.rideId
			where rideSignup.memberId=? and pending=0
			order by ride.rideDate asc limit 1';

		$input = [\App\Model\Session::getSignedInMemberId(), ];
		$ride = new \App\Record\Ride();
		$ride->loadFromSQL($sql, $input);

		return $ride;
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public function getMyPendingRides(\App\Record\Member $member) : \PHPFUI\ORM\RecordCursor
		{
		$this->setLimit(0);
		$condition = new \PHPFUI\ORM\Condition('memberId', $member->memberId);
		$condition->and('pending', 1);
		$this->addOrderBy('rideDate');
		$this->setWhere($condition);

		return $this->getRecordCursor();
		}

	public static function getNewest() : \App\Record\Ride
		{
		$sql = 'select * from ride where pending=0 order by rideDate desc limit 1';

		$ride = new \App\Record\Ride();
		$ride->loadFromSQL($sql);

		return $ride;
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public function getNewlyAddedUpcomingRides(string $start, string $end = '', int $pending = 0) : \PHPFUI\ORM\RecordCursor
		{
		$this->setLimit(0);

		if (! $end)
			{
			$end = \date('Y-m-d H:i:s', \strtotime($start) + 3600);
			}
		$rideDate = \date('Y-m-d', \strtotime($start) + 14 * 24 * 3600);
		$condition = new \PHPFUI\ORM\Condition('dateAdded', $start, new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('dateAdded', $end, new \PHPFUI\ORM\Operator\LessThanEqual());
		$condition->and('rideDate', $rideDate, new \PHPFUI\ORM\Operator\LessThanEqual());
		$condition->and('pending', $pending);
		$this->setWhere($condition);

		return $this->getRecordCursor();
		}

	public static function getOldest() : \App\Record\Ride
		{
		$sql = 'select * from ride where rideDate>"2000-01-01" and pending=0 order by rideDate, rideId limit 1';

		$ride = new \App\Record\Ride();
		$ride->loadFromSQL($sql);

		return $ride;
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public function getRidesForLocation(int $startLocationId, string $date = '') : \PHPFUI\ORM\RecordCursor
		{
		$this->setLimit(0);
		$condition = new \PHPFUI\ORM\Condition('startLocationId', $startLocationId);
		$condition->and('pending', 0);

		if ($date)
			{
			$condition->and('rideDate', $date);
			}
		$this->setWhere($condition);
		$this->setOrderBy('rideDate', 'desc');

		return $this->getRecordCursor();
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public function getRideStatus(string $startDate, string $endDate) : \PHPFUI\ORM\RecordCursor
		{
		$this->setLimit(0);
		$condition = new \PHPFUI\ORM\Condition('rideStatus', 0, new \PHPFUI\ORM\Operator\GreaterThan());
		$condition->and('rideStatus', 3, new \PHPFUI\ORM\Operator\NotEqual());
		$condition->and('pending', 0);
		$condition->and('rideDate', $startDate, new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('rideDate', $endDate, new \PHPFUI\ORM\Operator\LessThanEqual());
		$this->setWhere($condition);

		return $this->getRecordCursor();
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public function getRideStatusUnawarded(string $startDate, string $endDate) : \PHPFUI\ORM\RecordCursor
		{
		$this->setLimit(0);
		$condition = new \PHPFUI\ORM\Condition('rideDate', $startDate, new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('rideDate', $endDate, new \PHPFUI\ORM\Operator\LessThanEqual());
		$condition->and('unaffiliated', 0);
		$condition->and('pending', 0);

		$noPoints = new \PHPFUI\ORM\Condition('rideStatus', 0, new \PHPFUI\ORM\Operator\GreaterThan());
		$noPoints->and('pointsAwarded', 0);

		$hasPoints = new \PHPFUI\ORM\Condition('rideStatus', 0);
		$hasPoints->and('pointsAwarded', 0, new \PHPFUI\ORM\Operator\GreaterThan());

		$or = new \PHPFUI\ORM\Condition();
		$or->or($noPoints);
		$or->or($hasPoints);

		$condition->and($or);

		$this->setWhere($condition);

		return $this->getRecordCursor();
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public function getRWGPSStats(\App\Record\RWGPS $rwgps) : \PHPFUI\ORM\RecordCursor
		{
		$this->setLimit(0);
		$this->setJoin('rideRWGPS');
		$condition = new \PHPFUI\ORM\Condition('rideRWGPS.RWGPSId', $rwgps->RWGPSId);
		$condition->and('elevation', 0, new \PHPFUI\ORM\Operator\GreaterThan());
		$condition->and('pending', 0);
		$condition->and('rideStatus', \App\Enum\Ride\Status::COMPLETED->value);
		$this->setWhere($condition);

		return $this->getRecordCursor();
		}

	/**
	 * @return array<string>
	 */
	public static function getStatusValues() : array
		{
		return [
			\App\Enum\Ride\Status::NOT_YET->value => 'Not Yet',
			\App\Enum\Ride\Status::CANCELLED_FOR_WEATHER->value => 'Canceled for CANCELLED_FOR_WEATHER',
			\App\Enum\Ride\Status::NO_RIDERS_SHOWED->value => 'No Riders Showed',
			\App\Enum\Ride\Status::LEADER_OPTED_OUT->value => 'Leader Opted Out',
			\App\Enum\Ride\Status::CUT_SHORT->value => 'Cut Short',
			\App\Enum\Ride\Status::COMPLETED->value => 'Completed',
		];
		}

	public static function latestRideForAssistant(int $memberId) : \App\Record\Ride
		{
		$sql = 'select r.* from ride r
			left join assistantLeader al on al.rideId=r.rideId
			where al.memberId=? and r.pending=0 order by r.rideDate desc limit 1';
		$input = [$memberId, ];

		$ride = new \App\Record\Ride();
		$ride->loadFromSQL($sql, $input);

		return $ride;
		}

	public static function latestRideForMember(int $memberId) : \App\Record\Ride
		{
		$sql = 'select * from ride where memberId=? and pending=0 order by rideDate desc limit 1';
		$input = [$memberId, ];

		$ride = new \App\Record\Ride();
		$ride->loadFromSQL($sql, $input);

		return $ride;
		}

	public static function oldestRideForAssistant(int $memberId) : \App\Record\Ride
		{
		$sql = 'select r.* from ride r
			left join assistantLeader al on al.rideId=r.rideId
			where al.memberId=? and r.pending=0 order by r.rideDate limit 1';
		$input = [$memberId, ];

		$ride = new \App\Record\Ride();
		$ride->loadFromSQL($sql, $input);

		return $ride;
		}

	public static function oldestRideForMember(int $memberId) : \App\Record\Ride
		{
		$sql = 'select * from ride where memberId=? and pending=0 order by rideDate limit 1';
		$input = [$memberId, ];

		$ride = new \App\Record\Ride();
		$ride->loadFromSQL($sql, $input);

		return $ride;
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public function pastRidesForAssistant(\App\Record\Member $member, int $limit = 50, int $year = 0) : \PHPFUI\ORM\RecordCursor
		{
		$input = [$member->memberId];
		$this->setSelect('ride.*');
		$this->setJoin('assistantLeader');
		$condition = new \PHPFUI\ORM\Condition('assistantLeader.memberId', $member->memberId);
		$condition->and('pending', 0);

		$or = new \PHPFUI\ORM\Condition('rideStatus', 1, new \PHPFUI\ORM\Operator\GreaterThan());
		$or->or('unaffiliated', 0);
		$condition->and($or);

		if ($year)
			{
			$input[] = \App\Tools\Date::toString(\min(\App\Tools\Date::make($year, 12, 31), \App\Tools\Date::today()));
			$input[] = \App\Tools\Date::makeString($year, 1, 1);
			$condition->and('rideDate', \App\Tools\Date::toString(\min(\App\Tools\Date::make($year, 12, 31), \App\Tools\Date::today())), new \PHPFUI\ORM\Operator\LessThanEqual());
			$condition->and('rideDate', \App\Tools\Date::makeString($year, 1, 1), new \PHPFUI\ORM\Operator\GreaterThanEqual());
			}
		else
			{
			$condition->and('rideDate', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\LessThanEqual());
			}
		$this->setWhere($condition);
		$this->setOrderBy('rideDate', 'desc');
		$this->setLimit($limit);

		return $this->getRecordCursor();
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public function pastRidesForMember(\App\Record\Member $member, int $limit = 50, int $year = 0) : \PHPFUI\ORM\RecordCursor
		{
		$condition = new \PHPFUI\ORM\Condition('memberId', $member->memberId);
		$condition->and('pending', 0);

		$status = new \PHPFUI\ORM\Condition('unaffiliated', 0);
		$status->or('rideStatus', 1, new \PHPFUI\ORM\Operator\GreaterThan());
		$condition->and($status);

		if ($year)
			{
			$condition->and('rideDate', \App\Tools\Date::toString(\min(\App\Tools\Date::make($year, 12, 31), \App\Tools\Date::today())), new \PHPFUI\ORM\Operator\LessThanEqual());
			$condition->and('rideDate', \App\Tools\Date::makeString($year, 1, 1), new \PHPFUI\ORM\Operator\GreaterThanEqual());
			}
		else
			{
			$condition->and('rideDate', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\LessThanEqual());
			}
		$this->setWhere($condition);
		$this->setOrderBy('rideDate', 'desc');
		$this->setLimit($limit);

		return $this->getRecordCursor();
		}

	public function setInventoryBySignup(string $rideDate) : static
		{
		$this->setJoin('startLocation');
		$this->addJoin('rideSignup', new \PHPFUI\ORM\Condition('ride.rideId', new \PHPFUI\ORM\Field('rideSignup.rideId')));
		$this->addJoin('member', new \PHPFUI\ORM\Condition('member.memberId', new \PHPFUI\ORM\Field('rideSignup.memberId')));
		$this->addJoin('invoice', new \PHPFUI\ORM\Condition('invoice.memberId', new \PHPFUI\ORM\Field('member.memberId')));
		$this->addJoin('invoiceItem', new \PHPFUI\ORM\Condition('invoice.invoiceId', new \PHPFUI\ORM\Field('invoiceItem.invoiceId')));
		$whereCondition = new \PHPFUI\ORM\Condition('rideDate', $rideDate);
		$whereCondition->and(new \PHPFUI\ORM\Condition('fullfillmentDate', null, new \PHPFUI\ORM\Operator\IsNull()));
		$whereCondition->and(new \PHPFUI\ORM\Condition('type', \App\Enum\Store\Type::ORDER));
		$this->setWhere($whereCondition);
		$this->addOrderBy('ride.title');
		$this->addOrderBy('member.lastName');
		$this->addOrderBy('member.firstName');
		$this->addSelect('ride.title', 'Ride');
		$this->addSelect(new \PHPFUI\ORM\Literal('concat(member.firstName," ",member.lastName)'), 'Name');
		$this->addSelect('invoiceItem.title', 'Description');
		$this->addSelect('invoiceItem.detailLine', 'Detail');
		$this->addSelect('invoiceItem.quantity', 'Quantity');

		return $this;
		}

	public function setRidesForCueSheetCursor(\App\Record\CueSheet $cuesheet) : static
		{
		$this->setWhere(new \PHPFUI\ORM\Condition('cueSheetId', $cuesheet->cueSheetId));
		$this->addOrderBy('rideDate', 'desc');

		return $this;
		}

	public function setRidesForLocationCursor(\App\Record\StartLocation $startLocation, string $date = '') : static
		{
		$condition = new \PHPFUI\ORM\Condition('startLocationId', $startLocation->startLocationId);

		if ($date)
			{
			$condition->and('rideDate', $date);
			}
		$this->setWhere($condition);
		$this->addOrderBy('rideDate', 'desc');

		return $this;
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public function unreportedRides() : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'select * from ride r
			where r.rideStatus=0 and r.rideStatus=0 and r.rideDate<? and r.unaffiliated=0 and r.pending=0
			order by r.rideDate desc limit 50';

		$this->setLimit(50);
		$this->setOrderBy('rideDate', 'desc');
		$this->addOrderBy('rideId');
		$condition = new \PHPFUI\ORM\Condition('rideStatus', 0);
		$condition->and('unaffiliated', 0);
		$condition->and('pending', 0);
		$condition->and('rideDate', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\LessThan());
		$this->setWhere($condition);

		return $this->getRecordCursor();
		}

	public function unreportedRidesForMember(int $memberId) : \PHPFUI\ORM\RecordCursor
		{
		$this->setLimit(10);
		$this->setOrderBy('rideDate', 'desc');
		$condition = new \PHPFUI\ORM\Condition('rideStatus', 0);
		$condition->and('memberId', $memberId);
		$condition->and('unaffiliated', 0);
		$condition->and('rideStatus', 0);
		$condition->and('pending', 0);
		$condition->and('rideDate', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\LessThan());
		$this->setWhere($condition);

		return $this->getRecordCursor();
		}

	/**
	 * @param array<string> $dates
	 *
	 * @return ?\PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public function unreportedRidesOn(array $dates) : ?\PHPFUI\ORM\RecordCursor
		{
		if (! $dates)
			{
			return null;
			}
		$this->setLimit(0);
		$condition = new \PHPFUI\ORM\Condition('unaffiliated', 0);
		$condition->and('rideStatus', 0);
		$condition->and('pending', 0);
		$condition->and('rideDate', $dates, new \PHPFUI\ORM\Operator\In());
		$this->setWhere($condition);
		$this->setOrderBy('rideId');

		return $this->getRecordCursor();
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public function upcomingRides(int $limit = 0) : \PHPFUI\ORM\RecordCursor
		{
		$input = [\App\Tools\Date::todayString()];

		$this->setSelect('ride.*');
		$this->addSelect('pace.*');
		$this->setJoin('pace');
		$condition = new \PHPFUI\ORM\Condition('rideDate', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('pending', 0);
		$this->setWhere($condition);
		$this->addOrderBy('rideDate')->addOrderBy('pace.ordering')->addOrderBy('targetPace', 'desc')->addOrderBy('mileage', 'desc');

		if ($limit)
			{
			$this->setLimit($limit);
			}

		return $this->getRecordCursor();
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public function with(\App\Record\Member $with) : \PHPFUI\ORM\RecordCursor
		{
		$this->setLimit(0);
		$this->addSelect('ride.*');
		$meCondition = new \PHPFUI\ORM\Condition('ride.rideId', new \PHPFUI\ORM\Literal('me.rideId'));
		$meCondition->and('me.memberId', \App\Model\Session::getSignedInMemberId());
		$this->setJoin('rideSignup', $meCondition, as: 'me');

		$themCondition = new \PHPFUI\ORM\Condition('ride.rideId', new \PHPFUI\ORM\Literal('them.rideId'));
		$themCondition->and('them.memberId', $with->memberId);
		$this->addJoin('rideSignup', $themCondition, as: 'them');

		$this->setWhere(new \PHPFUI\ORM\Condition('me.rideId', new \PHPFUI\ORM\Literal('them.rideId')));
		$this->addOrderBy('rideDate', 'desc');

		return $this->getRecordCursor();
		}
	}
