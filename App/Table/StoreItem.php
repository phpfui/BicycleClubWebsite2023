<?php

namespace App\Table;

class StoreItem extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\StoreItem::class;

	public static function byTitle(int $volunteerPoints = 0, bool $activeOnly = false) : iterable
		{
		$leadersOnly = $volunteerPoints ? '' : 'and pointsOnly=0';
		$active = $activeOnly ? 'and active=1 and (storeItemId in (select storeItemId from storeItemDetail where storeItemId=storeItemId and quantity>0)' .
			' or storeItemId in (select storeItemId from storeItemOption where storeItemId=storeItemId))' : '';
		$sql = "select * from storeItem where type=0 {$leadersOnly} {$active} order by title";

		return \PHPFUI\ORM::getArrayCursor($sql);
		}
	}
