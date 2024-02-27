<?php

namespace App\Table;

class CueSheet extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\CueSheet::class;

	/**
	 * @return \PHPFUI\ORM\RecordCursor<\App\Record\CueSheet>
	 */
	public static function getApprovedUnawarded(string $startDate, string $endDate = '') : \PHPFUI\ORM\RecordCursor
		{
		if (! $endDate)
			{
			$endDate = \App\Tools\Date::todayString();
			}
		$sql = 'select * from cueSheet where dateAdded>=? and dateAdded<=? and pending=0 and pointsAwarded=0';

		return \PHPFUI\ORM::getRecordCursor(new \App\Record\CueSheet(), $sql, [$startDate, $endDate]);
		}

	/**
	 * @return array<string,array<string,int>>
	 */
	public static function getCountByStartLocation() : array
		{
		$sql = 'select startLocationId,count(cueSheetId) as count,cueSheetId from cueSheet where startLocationId>0 group by startLocationId';
		$cueSheet = \PHPFUI\ORM::getDataObjectCursor($sql, []);
		$map = [];

		foreach ($cueSheet as $cuesheet)
			{
			$map[$cuesheet['startLocationId']]['count'] = $cuesheet['count'];
			$map[$cuesheet['startLocationId']]['cueSheetId'] = $cuesheet['cueSheetId'];
			}

		return $map;
		}

	public static function getCueSheetsForLocation(int $location) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = self::getSelectedFields() . ' where c.startLocationId=? order by c.mileage, c.name';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$location]);
		}

	public static function getForDateRange(string $startDate, string $endDate) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = 'select * from cueSheet where pending=0 and dateAdded >= ? and dateAdded <= ?';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$startDate, $endDate]);
		}

	public static function getForMemberDate(int $memberId, string $startDate, string $endDate) : \PHPFUI\ORM\DataObjectCursor
		{
		$sql = self::getSelectedFields() . ' where c.pending!=0 and c.memberId=? and c.dateAdded>=? and c.dateAdded<=? order by c.dateAdded desc';

		return \PHPFUI\ORM::getDataObjectCursor($sql, [$memberId, $startDate, $endDate]);
		}

	public function setFromMemberCursor(int $memberId) : static
		{
		$this->addOrderBy('dateAdded', 'desc');
		$this->setWhere(new \PHPFUI\ORM\Condition('memberId', $memberId));

		return $this;
		}

	public function setPendingCursor() : static
		{
		$this->setWhere(new \PHPFUI\ORM\Condition('pending', 0, new \PHPFUI\ORM\Operator\GreaterThan()));
		$this->addOrderBy('dateAdded', 'desc');
		$this->setLimit(10);

		return $this;
		}

	public function setRecentlyAddedCursor(int $limit = 10) : static
		{
		$this->addOrderBy('dateAdded', 'desc');
		$this->setWhere(new \PHPFUI\ORM\Condition('dateAdded', \App\Tools\Date::todayString(-90), new \PHPFUI\ORM\Operator\GreaterThan()));
		$this->setLimit($limit);

		return $this;
		}

	private static function getSelectedFields() : string
		{
		return 'select c.*,s.name as locationName
			from cueSheet c
			left join member m on c.memberId=m.memberId
			left join startLocation s on s.startLocationId=c.startLocationId ';
		}
	}
