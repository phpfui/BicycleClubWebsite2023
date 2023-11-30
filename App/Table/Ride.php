<?php

namespace App\Table;

class Ride extends \PHPFUI\ORM\Table
	{
	final public const COMMENTS_DISABLED = 1;

	final public const COMMENTS_ENABLED = 0;

	final public const COMMENTS_HIDDEN = 2;

	final public const STATUS_COMPLETED = 5;

	final public const STATUS_CUT_SHORT = 4;

	final public const STATUS_NO_LEADER = 3;

	final public const STATUS_NO_RIDERS = 2;

	final public const STATUS_NOT_YET = 0;

	final public const STATUS_WEATHER = 1;

	protected static string $className = '\\' . \App\Record\Ride::class;

	public static function changePace(int $from, int $to) : bool
		{
		$sql = 'update ride set paceId=:to where paceId=:from';

		return \PHPFUI\ORM::execute($sql, ['from' => $from,
			'to' => $to, ]);
		}

	public function changeRWGPSId(int $old, ?int $new) : void
		{
		$sql = 'update ride set RWGPSId=:new where RWGPSId=:old';
		$input = ['new' => $new, 'old' => $old];

		\PHPFUI\ORM::execute($sql, $input);
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
	public static function getDateRange(int $start, int $end, string $sort = '') : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'select * from ride
			left join pace on pace.paceId=ride.paceId
			where rideDate >= ? and rideDate <= ? order by rideDate asc,pace.ordering asc,startTime asc,mileage ' . $sort;

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\Ride(), $sql, [\App\Tools\Date::toString($start), \App\Tools\Date::toString($end)]);
		}

	public static function getFirstRideWithCueSheet() : \App\Record\Ride
		{
		$sql = 'select * from ride where cueSheetId>0 and rideDate>"2000-01-01" order by rideDate limit 1';

		$ride = new \App\Record\Ride();
		$ride->loadFromSQL($sql);

		return $ride;
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public static function getForMemberDate(int $memberId, string $startDate, string $endDate) : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'select * from ride where memberId=? and rideDate>=? and rideDate<=? order by rideDate';

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
	 *
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public function getLeadersRides(array $categories, string $startDate, string $endDate) : \PHPFUI\ORM\RecordCursor
		{
		$statusCondition = new \PHPFUI\ORM\Condition('rideStatus', 1, new \PHPFUI\ORM\Operator\GreaterThan());
		$statusCondition->or('unaffiliated', 0);
		$condition = new \PHPFUI\ORM\Condition('rideDate', $startDate, new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and(new \PHPFUI\ORM\Condition('rideDate', $endDate, new \PHPFUI\ORM\Operator\LessThanEqual()));
		$condition->and(new \PHPFUI\ORM\Condition('memberId', 0, new \PHPFUI\ORM\Operator\GreaterThan()));
		$condition->and($statusCondition);

		if (! empty($categories) && ! \in_array(0, $categories))
			{
			$condition->and(new \PHPFUI\ORM\Condition('paceId', $categories, new \PHPFUI\ORM\Operator\In()));
			}
		$this->addOrderBy('memberId')->addOrderBy('rideDate');
		$this->setWhere($condition);

		return $this->getRecordCursor();
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
					where rideDate >= ? and ride.paceId in
					(select pace.paceId from pace where categoryId in (' . \implode(',', $categories) . '))
					order by rideDate, pace.ordering, mileage limit 10;';

			return \PHPFUI\ORM::getRecordCursor(new \App\Record\Ride(), $sql, [\App\Tools\Date::todayString()]);
			}

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\Ride());
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public static function getMyDateRange(string $start, string $end, string $sort = '') : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'select ride.* from ride
			  left join pace on pace.paceId=ride.paceId
				left join rideSignup on rideSignup.rideId=ride.rideId
				where ride.rideDate >= ? and ride.rideDate <= ? and rideSignup.attended=? and rideSignup.memberId=?
				order by ride.rideDate asc,pace.ordering asc,ride.startTime asc,ride.mileage ' . $sort;
		$data = [$start, $end, \App\Table\RideSignup::CONFIRMED, \App\Model\Session::signedInMemberId()];

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\Ride(), $sql, $data);
		}

	public static function getMyNewest() : \App\Record\Ride
		{
		$sql = 'select ride.* from ride
			left join rideSignup on rideSignup.rideId=ride.rideId
			where rideSignup.attended=? and rideSignup.memberId=?
			order by ride.rideDate desc limit 1';

		$input = [\App\Table\RideSignup::CONFIRMED, \App\Model\Session::signedInMemberId(), ];

		$ride = new \App\Record\Ride();
		$ride->loadFromSQL($sql, $input);

		return $ride;
		}

	public static function getMyOldest() : \App\Record\Ride
		{
		$sql = 'select ride.* from ride
			left join rideSignup on rideSignup.rideId=ride.rideId
			where rideSignup.attended=? and rideSignup.memberId=?
			order by ride.rideDate asc limit 1';

		$input = [\App\Table\RideSignup::CONFIRMED, \App\Model\Session::signedInMemberId(), ];
		$ride = new \App\Record\Ride();
		$ride->loadFromSQL($sql, $input);

		return $ride;
		}

	public static function getNewest() : \App\Record\Ride
		{
		$sql = 'select * from ride order by rideDate desc limit 1';

		$ride = new \App\Record\Ride();
		$ride->loadFromSQL($sql);

		return $ride;
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public function getNewlyAddedUpcomingRides(string $start, string $end = '') : \PHPFUI\ORM\RecordCursor
		{
		if (! $end)
			{
			$end = \date('Y-m-d H:i:s', \strtotime($start) + 3600);
			}
		$sql = 'select * from ride where dateAdded>=? and dateAdded<=? and rideDate < ?';

		return \PHPFUI\ORM::getRecordCursor($this->instance, $sql, [$start, $end, \App\Tools\Date::todayString(10)]);
		}

	public static function getOldest() : \App\Record\Ride
		{
		$sql = 'select * from ride where rideDate>"2000-01-01" order by rideDate asc limit 1';

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
		$sql = 'select * from ride where startLocationId=?';

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
		$sql = 'select * from ride where rideStatus>0 and rideStatus!=3 and rideDate>=? and rideDate<=?';

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\Ride(), $sql, [$startDate, $endDate]);
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public static function getRideStatusUnawarded(string $startDate, string $endDate) : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'select * from ride where rideDate>=? and rideDate<=? and unaffiliated=0 and ((rideStatus>0 and pointsAwarded=0) or (rideStatus=0 and pointsAwarded>0))';

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\Ride(), $sql, [$startDate, $endDate]);
		}

	public function getRWGPSElevation(int $RWGPSId) : int
		{
		$sql = 'select AVG(elevation) from ride where RWGPSId = ? and elevation > 0 and rideStatus = ?';

		return (int)\round((int)\PHPFUI\ORM::getValue($sql, [$RWGPSId, self::STATUS_COMPLETED, ]));
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public function getRWGPSStats(\App\Record\RWGPS $rwgps) : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'select * from ride where RWGPSId = ? and elevation > 0 and rideStatus = ?';

		return \PHPFUI\ORM::getRecordCursor($this->instance, $sql, [$rwgps->RWGPSId, self::STATUS_COMPLETED, ]);
		}

	/**
	 * @return array<string>
	 */
	public static function getStatusValues() : array
		{
		return [
			self::STATUS_NOT_YET => 'Not Yet',
			self::STATUS_WEATHER => 'Canceled for Weather',
			self::STATUS_NO_RIDERS => 'No Riders Showed',
			self::STATUS_NO_LEADER => 'Leader Opted Out',
			self::STATUS_CUT_SHORT => 'Cut Short',
			self::STATUS_COMPLETED => 'Completed',
		];
		}

	public static function latestRideForAssistant(int $memberId) : \App\Record\Ride
		{
		$sql = 'select r.* from ride r
			left join assistantLeader al on al.rideId=r.rideId
			where al.memberId=? and r.rideDate>2451549 order by r.rideDate desc limit 1';
		$input = [$memberId, ];

		$ride = new \App\Record\Ride();
		$ride->loadFromSQL($sql, $input);

		return $ride;
		}

	public static function latestRideForMember(int $memberId) : \App\Record\Ride
		{
		$sql = 'select * from ride where memberId=? and rideDate>0 order by rideDate desc limit 1';
		$input = [$memberId, ];

		$ride = new \App\Record\Ride();
		$ride->loadFromSQL($sql, $input);

		return $ride;
		}

	public static function oldestRideForAssistant(int $memberId) : \App\Record\Ride
		{
		$sql = 'select r.* from ride r
			left join assistantLeader al on al.rideId=r.rideId
			where al.memberId=? and r.rideDate>2451549 order by r.rideDate limit 1';
		$input = [$memberId, ];

		$ride = new \App\Record\Ride();
		$ride->loadFromSQL($sql, $input);

		return $ride;
		}

	public static function oldestRideForMember(int $memberId) : \App\Record\Ride
		{
		$sql = 'select * from ride where memberId=? and rideDate>2451549 order by rideDate limit 1';
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
			where al.memberId=? and (r.unaffiliated=0 or r.rideStatus>1) and ';

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
		$sql = 'select * from ride where memberId=? and (unaffiliated=0 or rideStatus>1) and ';

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
		$whereCondition->and(new \PHPFUI\ORM\Condition('type', \App\Model\Cart::TYPE_ORDER));
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
			where r.rideStatus=0 and r.rideStatus=0 and r.rideDate<? and r.unaffiliated=0 order by r.rideDate desc limit 50';

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\Ride(), $sql, [\App\Tools\Date::todayString()]);
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public static function unreportedRidesForMember(int $memberId) : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'select * from ride where memberId=? and unaffiliated=0 and rideStatus=0 and rideDate<=? and unaffiliated=0 order by rideDate desc limit 10';

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
			where r.unaffiliated=0 and r.rideStatus=0 and r.rideDate in ("' . $join . '")';

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\Ride(), $sql);
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\Ride>
	 */
	public static function upcomingRides(int $limit = 0) : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'select * from ride left join pace on pace.paceId=ride.paceId where rideDate >= ?';
		$input = [\App\Tools\Date::todayString()];

		$sql .= ' order by rideDate asc,pace.ordering asc,targetPace desc,mileage desc';

		if ($limit)
			{
			$sql .= ' limit ' . $limit;
			}

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\Ride(), $sql, $input);
		}
	}
