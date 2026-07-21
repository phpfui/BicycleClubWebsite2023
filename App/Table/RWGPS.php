<?php

namespace App\Table;

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
		$this->setLimit($limit);
		$and = new \PHPFUI\ORM\Condition('csv', '');
		$and->and('RWGPSId', 0, new \PHPFUI\ORM\Operator\GreaterThan());
		$condition = new \PHPFUI\ORM\Condition('lastSynced', \App\Tools\Date::todayString(-60), new \PHPFUI\ORM\Operator\LessThan());
		$condition->or('lastSynced', null);
		$condition->or($and);
		$this->setWhere($condition);
		$this->setOrderBy('lastUpdated');

		return $this->getRecordCursor();
		}

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\RWGPS>
	 */
	public function getUpcomingRWGPS() : \PHPFUI\ORM\RecordCursor
		{
//		$sql = 'select distinct RWGPS.*
//			from ride
//			left join rideRWGPS on rideRWGPS.rideId=ride.rideId
//			left join RWGPS on RWGPS.RWGPSId=rideRWGPS.RWGPSId
//			where rideDate>=:date and rideRWGPS.RWGPSId is not null';


		$rideTable = new \App\Table\Ride();
		$rideTable->setDistinct();
		$rideTable->setSelect('RWGPS.*');
		$rideTable->addJoin('rideRWGPS');
		$rideTable->addJoin('RWGPS', new \PHPFUI\ORM\Condition('RWGPS.RWGPSId', new \PHPFUI\ORM\Field('rideRWGPS.RWGPSId')));

		$condition = new \PHPFUI\ORM\Condition('rideDate', \App\Tools\Date::todayString(), new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('rideRWGPS.RWGPSId', null, new \PHPFUI\ORM\Operator\IsNotNull());
		$rideTable->setWhere($condition);

		return $rideTable->getRecordCursor($this->instance);
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
