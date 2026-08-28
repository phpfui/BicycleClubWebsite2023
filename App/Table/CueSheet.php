<?php

namespace App\Table;

class CueSheet extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\CueSheet::class;

	/**
	 * @return array<string,array<string,int>>
	 */
	public static function getCountByStartLocation() : array
		{
		$sql = 'select startLocationId,count(cueSheetId) as count,cueSheetId
			from cueSheet
			where startLocationId>0
			group by startLocationId';
		$cueSheet = \PHPFUI\ORM::getDataObjectCursor($sql); // OK
		$map = [];

		foreach ($cueSheet as $cuesheet)
			{
			$map[$cuesheet['startLocationId']]['count'] = $cuesheet['count'];
			$map[$cuesheet['startLocationId']]['cueSheetId'] = $cuesheet['cueSheetId'];
			}

		return $map;
		}

	public function getCueSheetsForLocation(int $locationId) : \PHPFUI\ORM\DataObjectCursor
		{
		$this->getSelectedFields();
		$this->setWhere(new \PHPFUI\ORM\Condition('cueSheet.startLocationId', $locationId));
		$this->setOrderBy('cueSheet.mileage');
		$this->addOrderBy('cueSheet.name');

		return $this->getDataObjectCursor();
		}

	public function getForDateRange(string $startDate, string $endDate) : \PHPFUI\ORM\DataObjectCursor
		{
		$condition = new \PHPFUI\ORM\Condition('pending', 0);
		$condition->and('dateAdded', $startDate, new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('dateAdded', $endDate, new \PHPFUI\ORM\Operator\LessThanEqual());
		$this->setWhere($condition);

		return $this->getDataObjectCursor();
		}

	public function getForMemberDate(int $memberId, string $startDate = '', string $endDate = '') : \PHPFUI\ORM\DataObjectCursor
		{
		$this->getSelectedFields();
		$condition = new \PHPFUI\ORM\Condition('pending', 0);
		$condition->and('cueSheet.memberId', $memberId);

		if ($startDate)
			{
			$condition->and('dateAdded', $startDate, new \PHPFUI\ORM\Operator\GreaterThanEqual());
			}

		if ($endDate)
			{
			$condition->and('dateAdded', $endDate, new \PHPFUI\ORM\Operator\LessThanEqual());
			}
		$this->setWhere($condition);
		$this->setOrderBy('dateAdded', 'desc');

		return $this->getDataObjectCursor();
		}

	public function setFromMemberCursor(int $memberId) : static
		{
		$this->setOrderBy('dateAdded', 'desc');
		$this->setWhere(new \PHPFUI\ORM\Condition('memberId', $memberId));

		return $this;
		}

	public function setPendingCursor() : static
		{
		$this->setWhere(new \PHPFUI\ORM\Condition('pending', 0, new \PHPFUI\ORM\Operator\GreaterThan()));
		$this->setOrderBy('dateAdded', 'desc');
		$this->setLimit(10);

		return $this;
		}

	public function setRecentlyAddedCursor(int $limit = 10) : static
		{
		$this->setOrderBy('dateAdded', 'desc');
		$this->setWhere(new \PHPFUI\ORM\Condition('dateAdded', \App\Tools\Date::todayString(-90), new \PHPFUI\ORM\Operator\GreaterThan()));
		$this->setLimit($limit);

		return $this;
		}

	private function getSelectedFields() : static
		{
		$this->setJoin('member');
		$this->addJoin('startLocation');
		$this->setSelect('cueSheet.*');
		$this->addSelect('startLocation.name', 'locationName');

		return $this;
		}
	}
