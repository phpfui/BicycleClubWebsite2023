<?php

namespace App\Table;

class StoreItemDetail extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\StoreItemDetail::class;

	public static function getAllStock(int $storeItemId, string $order = 'storeItemDetailId') : \PHPFUI\ORM\ArrayCursor
		{
		$sql = 'select * from storeItemDetail where storeItemId=? order by ' . $order;

		return \PHPFUI\ORM::getArrayCursor($sql, [$storeItemId]);
		}

	public static function getInStock(int $storeItemId, string $order = 'storeItemDetailId') : \PHPFUI\ORM\ArrayCursor
		{
		$sql = 'select * from storeItemDetail where storeItemId=? and quantity > 0 order by ' . $order;

		return \PHPFUI\ORM::getArrayCursor($sql, [$storeItemId]);
		}

	public static function getOutOfStock(int $storeItemId, string $order = 'storeItemDetailId') : \PHPFUI\ORM\ArrayCursor
		{
		$sql = 'select * from storeItemDetail where storeItemId=? and quantity <= 0 order by ' . $order;

		return \PHPFUI\ORM::getArrayCursor($sql, [$storeItemId]);
		}
	}
