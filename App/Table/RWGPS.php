<?php

namespace App\Table;

class RWGPS extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\RWGPS::class;

	public function closest(float $lat, float $long, int $limit = 1, float $distance = 0.5) : \PHPFUI\ORM\ArrayCursor
		{
		$sql = 'SELECT *,(3959*acos(cos(radians(:lat))*cos(radians(latitude))*cos(radians(longitude)-radians(:lon))+sin(radians(:lat))*sin(radians(latitude)))) AS distance FROM rwgps HAVING distance < :distance ORDER BY distance LIMIT :limit;';

		return \PHPFUI\ORM::getArrayCursor($sql, ['lat' => $lat, 'long' => $long, 'distance' => $distance, 'limit' => $limit]);
		}

	public function getMissing() : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'select * from rwgps where status >= 400 and status < 500';

		return \PHPFUI\ORM::getRecordCursor($this->instance, $sql);
		}

	public function getOldest(int $limit = 10) : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'select * from rwgps where (lastUpdated < ? or lastUpdated is null) or (csv = "" and RWGPSId>0) order by lastUpdated limit ' . $limit;
		$input = [\App\Tools\Date::todayString(-60)];

		return \PHPFUI\ORM::getRecordCursor($this->instance, $sql, $input);
		}

	public function getUpcomingEmptyRWGPS() : \PHPFUI\ORM\RecordCursor
		{
		$sql = 'select distinct rwgps.* from ride left join rwgps on rwgps.RWGPSId=ride.RWGPSId where rwgps.lastUpdated is null and ride.RWGPSId is not null and rideDate>=:date';

		return \PHPFUI\ORM::getRecordCursor($this->instance, $sql, ['date' => \App\Tools\Date::todayString()]);
		}

	public function setClubRides(array $RWGPSIds) : void
		{
		if ($RWGPSIds)
			{
			$sql = 'update rwgps set club=1 where RWGPSId in (' . \implode(',', $RWGPSIds) . ')';
			\PHPFUI\ORM::execute($sql);
			}
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

		$this->addJoin('ride', 'RWGPSId');
		$this->setOrderBy('rideDate');
		$condition = new \PHPFUI\ORM\Condition('rideDate', $startDate, new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('rideDate', $endDate, new \PHPFUI\ORM\Operator\LessThanEqual());
		$this->setWhere($condition);

		return $this;
		}
	}
