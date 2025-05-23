<?php

namespace App\Table;

class RWGPS extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\RWGPS::class;

	public function closest(float $lat, float $long, int $limit = 1, float $distance = 0.5) : \PHPFUI\ORM\ArrayCursor
		{
		$sql = 'SELECT *,(3959*acos(cos(radians(:lat))*cos(radians(latitude))*cos(radians(longitude)-radians(:lon))+sin(radians(:lat))*sin(radians(latitude)))) AS distance FROM RWGPS HAVING distance < :distance ORDER BY distance LIMIT :limit;';

		return \PHPFUI\ORM::getArrayCursor($sql, ['lat' => $lat, 'long' => $long, 'distance' => $distance, 'limit' => $limit]);
		}

	public function distanceFrom(float $latitude, float $longitude) : static
		{
		$this->addSelect('*');
		$this->addSelect(new \PHPFUI\ORM\Literal("ST_Distance_Sphere(POINT(latitude,longitude),POINT({$latitude},{$longitude}))"), 'meters');

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
