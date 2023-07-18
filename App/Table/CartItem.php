<?php

namespace App\Table;

class CartItem extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\CartItem::class;

	public function deleteDiscountForMember(int $memberId) : bool
		{
		$sql = 'delete from cartItem where discountCodeId is not null and memberId=?';

		return \PHPFUI\ORM::execute($sql, [$memberId]);
		}

	/**
	 * @return array<array<string, string>>
	 */
	public static function getCartFor(int $memberId) : array
		{
		$sql = 'select i.*,d.detailLine,c.* from cartItem c
			left join storeItem i on i.storeItemId=c.storeItemId
			left join storeItemDetail d on d.storeItemId=c.storeItemId and d.storeItemDetailId=c.storeItemDetailId
			where c.memberId=?';

		return \PHPFUI\ORM::getRows($sql, [$memberId]);
		}

	public static function getItemCountForMember(string $validItemNumbers, int $customerNumber) : int
		{
		$itemNumbers = \explode(',', $validItemNumbers);
		$sql = 'select count(*) from cartItem where memberId=' . (int)$customerNumber . ' and (';
		$or = '';

		foreach ($itemNumbers as $item)
			{
			$ids = \explode('-', $item);

			if (1 == \count($ids))
				{
				$itemId = (int)($ids[0]);
				$sql .= "{$or} storeItemId={$itemId}";
				}
			else
				{
				$itemId = (int)($ids[0]);
				$detailId = (int)($ids[1]);
				$sql .= "{$or} (storeItemId={$itemId} and storeItemId={$detailId})";
				}
			$or = ' or ';
			}
		$sql .= ')';

		return (int)\PHPFUI\ORM::getValue($sql);
		}

	public static function purgeOldItems(string $strDate) : bool
		{
		$sql = 'delete from cartItem where added<?';

		return \PHPFUI\ORM::execute($sql, [$strDate]);
		}
	}
