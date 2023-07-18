<?php

namespace App\Table;

class Banner extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\Banner::class;

	/**
	 * @return array<array<string,string>>
	 */
	public function getActiveRows() : array
		{
		$sql = 'select * from banner where endDate>=? and startDate<=? and pending=0';

		return \PHPFUI\ORM::getRows($sql, [\App\Tools\Date::todayString(), \App\Tools\Date::todayString()]);
		}

	public function getOldest() : \App\Record\Banner
		{
		$sql = 'select * from banner order by endDate limit 1';

		$record = new \App\Record\Banner();
		$record->loadFromSQL($sql);

		return $record;
		}
	}
