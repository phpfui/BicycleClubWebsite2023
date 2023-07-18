<?php

namespace App\Table;

class DiscountCode extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\DiscountCode::class;

	public function getAllCodes() : \PHPFUI\ORM\ArrayCursor
		{
		$sql = 'select d.*,count(i.discountCodeId) used from discountCode d left join invoice i on i.discountCodeId=d.discountCodeId group by discountCodeId order by d.discountCode';

		return \PHPFUI\ORM::getArrayCursor($sql);
		}
	}
