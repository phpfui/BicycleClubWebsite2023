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
		$this->addSelect('ride.rideId');
		$this->addSelect('ride.rideDate');
		$this->addSelect('ride.title', 'name');
		$this->addSelect('firstName');
		$this->addSelect('lastName');
		$this->addSelect('RWGPS.RWGPSId');
		$this->addSelect('RWGPS.title');
		$this->addSelect('RWGPS.latitude');
		$this->addSelect('RWGPS.longitude');
		$this->addSelect(new \PHPFUI\ORM\Literal("ST_Distance_Sphere(POINT(RWGPS.latitude,RWGPS.longitude),POINT({$latitude},{$longitude}))"), 'meters');
		$this->addJoin('rideRWGPS');
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
		$this->addJoin('pace');

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
	public static function futureRidesForMember(\App\Record\Member $member) : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'select * from ride where memberId=? and rideDate>=? order by rideDate';

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\Ride(), $sql, [$member->memberId, \App\Tools\Date::todayString(), ]);
		}

	/**
	 * @param array<int> $categories
	 *
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public static function getAssistantLeadersRides(int $assistantLeader, array $categories, int $startDate, int $endDate) : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'select r.* from ride r
			left join assistantLeader al on r.rideId=al.rideId
			where al.memberId=? and ';
		$input = [$assistantLeader];

		if (! empty($categories) && ! \in_array(0, $categories))
			{
			$sql .= 'r.paceId in (';
			$comma = '';

			foreach ($categories as $id)
				{
				$sql .= $comma . '?';
				$input[] = $id;
				$comma = ',';
				}
			$sql .= ') and ';
			}
		$sql .= 'r.rideDate >= ? and r.rideDate <= ? and r.memberId > 0 and (r.unaffiliated=0 or r.rideStatus>1) order by r.memberId';
		$input[] = $startDate;
		$input[] = $endDate;

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\Ride(), $sql, $input);
		}

	/**
	 * @return array<int,array<string,int>>
	 */
	public static function getCountByStartLocation() : array
		{
		$sql = 'select startLocationId,count(rideId) as count,rideId from ride where startLocationId>0 group by startLocationId';
		$rides = \PHPFUI\ORM::getDataObjectCursor($sql, []);
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

	public static function getCuesheetStats(int $year) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select c.name cuesheetname,s.name,s.link,s.directions,c.cueSheetId,c.startLocationId,count(r.cueSheetId) count from ride r
							inner join cueSheet c on r.cueSheetId=c.cueSheetId
							inner join startLocation s on s.startLocationId=c.startLocationId
							where r.rideDate >= ? and r.rideDate <= ? and r.cueSheetId>0
							group by c.cueSheetId
							order by count desc';
		$input = [\App\Tools\Date::makeString($year, 1, 1), \App\Tools\Date::makeString($year, 12, 31), ];

		return \PHPFUI\ORM::getDataObjectCursor($sql, $input);
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public static function getDateRange(int $start, int $end) : \PHPFUI\ORM\RecordCursor
		{
		$rideTable = new \App\Table\Ride();
		$rideTable->addSelect('ride.*');
		$rideTable->addJoin('pace');
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
	public static function getForMemberDate(int $memberId, string $startDate, string $endDate) : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'select * from ride where memberId=? and rideDate>=? and rideDate<=? and pending=0 order by rideDate';

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\Ride(), $sql, [$memberId, $startDate, $endDate]);
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
			$result->addSelect('ride.*');

			if ($leaderType)
				{
				$assistantLeaderCondition = new \PHPFUI\ORM\Condition();
				$result->addJoin('assistantLeader');
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
	public static function getMyCategoryRides(\App\Record\Member $member) : \PHPFUI\ORM\RecordCursor
		{
		$categories = \App\Table\MemberCategory::getRideCategoriesForMember($member->memberId);

		if (\count($categories))
			{
			$sql = 'select * from ride
					left join pace on pace.paceId=ride.paceId
					where rideDate >= ? and pending=0 and ride.paceId in
					(select pace.paceId from pace where categoryId in (' . \implode(',', $categories) . '))
					order by rideDate, pace.ordering, mileage limit 10;';

			return \PHPFUI\ORM::getRecordCursor(new \App\Record\Ride(), $sql, [\App\Tools\Date::todayString()]);
			}

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\Ride());
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public function getMyDateRange(string $start, string $end, \App\Enum\RideSignup\Attended $status) : \PHPFUI\ORM\RecordCursor
		{
		$this->addSelect('ride.*');
		$this->addJoin('pace');
		$this->addJoin('rideSignup');
		$this->addOrderBy('ride.rideDate')->addOrderBy('pace.ordering')->addOrderBy('ride.startTime')->addOrderBy('ride.mileage');
		$condition = new \PHPFUI\ORM\Condition('ride.rideDate', $start, new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('ride.rideDate', $end, new \PHPFUI\ORM\Operator\LessThanEqual());
		$condition->and('ride.pending', 0);
		$condition->and('rideSignup.memberId', \App\Model\Session::signedInMemberId());

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
			where rideSignup.attended=? and rideSignup.memberId=? and pending=0
			order by ride.rideDate desc limit 1';

		$input = [\App\Enum\RideSignup\Attended::CONFIRMED->value, \App\Model\Session::signedInMemberId(), ];

		$ride = new \App\Record\Ride();
		$ride->loadFromSQL($sql, $input);

		return $ride;
		}

	public static function getMyOldest() : \App\Record\Ride
		{
		$sql = 'select ride.* from ride
			left join rideSignup on rideSignup.rideId=ride.rideId
			where rideSignup.attended=? and rideSignup.memberId=? and pending=0
			order by ride.rideDate asc limit 1';

		$input = [\App\Enum\RideSignup\Attended::CONFIRMED->value, \App\Model\Session::signedInMemberId(), ];
		$ride = new \App\Record\Ride();
		$ride->loadFromSQL($sql, $input);

		return $ride;
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public function getMyPendingRides(\App\Record\Member $member) : \PHPFUI\ORM\RecordCursor
		{
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
		if (! $end)
			{
			$end = \date('Y-m-d H:i:s', \strtotime($start) + 3600);
			}
		$rideDate = \date('Y-m-d', \strtotime($start) + 14 * 24 * 3600);
		$sql = 'select * from ride where dateAdded>=? and dateAdded<=? and pending = ? and rideDate <= ?';

		$input = [$start, $end, $pending, $rideDate];

		return \PHPFUI\ORM::getRecordCursor($this->instance, $sql, $input);
		}

	public static function getOldest() : \App\Record\Ride
		{
		$sql = 'select * from ride where rideDate>"2000-01-01" and pending=0 order by rideDate asc limit 1';

		$ride = new \App\Record\Ride();
		$ride->loadFromSQL($sql);

		return $ride;
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public static function getRidesForLocation(int $startLocationId, string $date = '') : \PHPFUI\ORM\RecordCursor
		{
		$data = [$startLocationId];
		$sql = 'select * from ride where startLocationId=? and pending=0';

		if ($date)
			{
			$sql .= ' and rideDate=?';
			$data[] = $date;
			}

		$sql .= ' order by rideDate desc';

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\Ride(), $sql, $data);
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public static function getRideStatus(string $startDate, string $endDate) : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'select * from ride where rideStatus>0 and rideStatus!=3 and rideDate>=? and rideDate<=? and pending=0';

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\Ride(), $sql, [$startDate, $endDate]);
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public static function getRideStatusUnawarded(string $startDate, string $endDate) : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'select * from ride where rideDate>=? and rideDate<=? and unaffiliated=0 and pending=0 and ((rideStatus>0 and pointsAwarded=0) or (rideStatus=0 and pointsAwarded>0))';

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\Ride(), $sql, [$startDate, $endDate]);
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public function getRWGPSStats(\App\Record\RWGPS $rwgps) : \PHPFUI\ORM\RecordCursor
		{
		$this->addJoin('rideRWGPS');
		$condition = new \PHPFUI\ORM\Condition('rideRWGPS.RWGPSId', $rwgps->RWGPSId);
		$condition->and('elevation', 0, new \PHPFUI\ORM\Operator\GreaterThan());
		$condition->and('pending', 0);
		$condition->and('rideStatus', \App\Enum\Ride\Status::COMPLETED->value);

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
	public static function pastRidesForAssistant(\App\Record\Member $member, int $limit = 50, int $year = 0) : \PHPFUI\ORM\RecordCursor
		{
		$input = [$member->memberId];
		$sql = 'select r.* from ride r
			left join assistantLeader al on al.rideId=r.rideId
			where al.memberId=? and (r.unaffiliated=0 or r.rideStatus>1) and r.pending=0 and ';

		if ($year)
			{
			$sql .= 'r.rideDate<=? and r.rideDate>=?';
			$input[] = \App\Tools\Date::toString(\min(\App\Tools\Date::make($year, 12, 31), \App\Tools\Date::today()));
			$input[] = \App\Tools\Date::makeString($year, 1, 1);
			}
		else
			{
			$sql .= 'r.rideDate<=?';
			$input[] = \App\Tools\Date::todayString();
			}
		$sql .= ' order by r.rideDate desc';

		if ($limit)
			{
			$sql .= ' limit ' . (int)$limit;
			}

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\Ride(), $sql, $input);
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public static function pastRidesForMember(\App\Record\Member $member, int $limit = 50, int $year = 0) : \PHPFUI\ORM\RecordCursor
		{
		$input = [$member->memberId];
		$sql = 'select * from ride where memberId=? and (unaffiliated=0 or rideStatus>1) and pending=0 and ';

		if ($year)
			{
			$sql .= 'rideDate<=? and rideDate>=?';
			$input[] = \App\Tools\Date::toString(\min(\App\Tools\Date::make($year, 12, 31), \App\Tools\Date::today()));
			$input[] = \App\Tools\Date::makeString($year, 1, 1);
			}
		else
			{
			$sql .= 'rideDate<=?';
			$input[] = \App\Tools\Date::todayString();
			}
		$sql .= ' order by rideDate desc';

		if ($limit)
			{
			$sql .= ' limit ' . (int)$limit;
			}

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\Ride(), $sql, $input);
		}

	public function setInventoryBySignup(string $rideDate) : static
		{
		$this->addJoin('startLocation');
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
	public static function unreportedRides() : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'select * from ride r
			where r.rideStatus=0 and r.rideStatus=0 and r.rideDate<? and r.unaffiliated=0 and r.pending=0 order by r.rideDate desc limit 50';

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\Ride(), $sql, [\App\Tools\Date::todayString()]);
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public static function unreportedRidesForMember(int $memberId) : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'select * from ride where memberId=? and unaffiliated=0 and rideStatus=0 and rideDate<=? and pending=0 and unaffiliated=0 order by rideDate desc limit 10';

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\Ride(), $sql, [$memberId, \App\Tools\Date::todayString(), ]);
		}

	/**
	 * @param array<string> $dates
	 */
	public static function unreportedRidesOn(array $dates) : ?\PHPFUI\ORM\RecordCursor
		{
		if (! $dates)
			{
			return null;
			}
		$join = \implode('","', $dates);
		$sql = 'select * from ride r
			left join member m on m.memberId=r.memberId
			where r.unaffiliated=0 and r.rideStatus=0 and r.pending=0 and r.rideDate in ("' . $join . '")';

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\Ride(), $sql);
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public static function upcomingRides(int $limit = 0) : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'select * from ride left join pace on pace.paceId=ride.paceId where rideDate >= ? and pending=0';
		$input = [\App\Tools\Date::todayString()];

		$sql .= ' order by rideDate asc,pace.ordering asc,targetPace desc,mileage desc';

		if ($limit)
			{
			$sql .= ' limit ' . $limit;
			}

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\Ride(), $sql, $input);
		}
	}
