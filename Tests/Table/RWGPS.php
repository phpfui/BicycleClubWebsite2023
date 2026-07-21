<?php

namespace Tests\Table;

class RWGPS extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\RWGPS::class;

	public function closest(float $lat, float $long, int $limit = 1, float $distance = 0.5) : \PHPFUI\ORM\ArrayCursor
		{
		$this->setSelect('*');
		$formula = "(3959*ACOS(COS(RADIANS({$lat}))*COS(RADIANS(latitude))*COS(RADIANS(longitude)-RADIANS({$long}))+SIN(RADIANS({$lat}))*SIN(RADIANS(latitude))))";
		$this->addSelect(new \PHPFUI\ORM\Literal($formula), 'distance');
		$this->setGroupBy('RWGPSId');
		$this->setHaving(new \PHPFUI\ORM\Condition('distance', $distance, new \PHPFUI\ORM\Operator\LessThan()));
		$this->setOrderBy('distance');
		$this->setLimit($limit);

		return $this->getArrayCursor();
		}

	public function distanceFrom(float $latitude, float $longitude) : static
		{
		$this->addSelect('*');
		$formula = "(6371000 * ACOS(COS(RADIANS({$latitude})) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS({$longitude})) + SIN(RADIANS({$latitude})) * SIN(RADIANS(latitude))))";
		$this->addSelect(new \PHPFUI\ORM\Literal($formula), 'meters');

		return $this;
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\RWGPS>
	 */
	public function getOldest(int $limit = 10) : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'select * from RWGPS where (lastSynced < ? or lastSynced is null) or (csv = "" and RWGPSId>0) order by lastUpdated limit ' . $limit;
		$input = [\App\Tools\Date::todayString(-60)];

		return \PHPFUI\ORM::getRecordCursor($this->instance, $sql, $input);
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\RWGPS>
	 */
	public function getUpcomingRWGPS() : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'select distinct RWGPS.*
			from ride
			left join rideRWGPS on rideRWGPS.rideId=ride.rideId
			left join RWGPS on RWGPS.RWGPSId=rideRWGPS.RWGPSId
			where rideDate>=:date and rideRWGPS.RWGPSId is not null';

		return \PHPFUI\ORM::getRecordCursor($this->instance, $sql, ['date' => \App\Tools\Date::todayString()]);
		}

	public function setNonClubBetween(string $startDate = '', string $endDate = '') : static
		{
		if (! $startDate)
			{
			$startDate = \App\Tools\Date::todayString();
			}

		if (! $endDate)
			{
			$endDate = \App\Tools\Date::toString(\App\Tools\Date::fromString($startDate) + 30);
			}

		$this->addJoin('rideRWGPS');
		$rwgpsJoin = new \PHPFUI\ORM\Condition('ride.rideId', new \PHPFUI\ORM\Literal('rideRWGPS.rideId'));
		$this->addJoin('ride', $rwgpsJoin);
		$this->setOrderBy('rideDate');
		$condition = new \PHPFUI\ORM\Condition('rideDate', $startDate, new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('rideDate', $endDate, new \PHPFUI\ORM\Operator\LessThanEqual());
		$this->setWhere($condition);

		return $this;
		}
	}
