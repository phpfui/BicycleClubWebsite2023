<?php

namespace App\Model;

class DiscountCode
	{
	public function __construct(private readonly ?\App\Record\DiscountCode $discountCode)
		{
		}

	/**
	 * @param array<array<string, string>>|\PHPFUI\ORM\DataObjectCursor<\App\Record\InvoiceItem> $items
	 */
	public function computeDiscount(array|\PHPFUI\ORM\DataObjectCursor $items, float $cashAmount) : float
		{
		$discount = $total = 0.0;

		if (! $this->discountCode)
			{
			return $discount;
			}

		if ($this->discountCode->empty())
			{
			return $discount;
			}

		$eligibleItemNumbers = \explode(',', $this->discountCode->validItemNumbers);

		$eligibleCount = 0;

		foreach ($items as $cartItem)
			{
			if ($this->isEligible($cartItem, $eligibleItemNumbers))
				{
				$quantity = (int)$cartItem['quantity'];
				$total += $quantity * (float)$cartItem['price'];
				$eligibleCount += $quantity;
				}
			}

		if ($this->discountCode->cashOnly)
			{
			$discountableAmount = $cashAmount;
			}
		else // discount on whole price
			{
			$discountableAmount = $total;
			}

		if (\App\Enum\Store\DiscountType::PERCENT_OFF == $this->discountCode->type) // @phpstan-ignore-line
			{
			$discount = $discountableAmount * ((float)$this->discountCode->discount) / 100;
			}
		else
			{
			$numberDiscounts = \min($eligibleCount, \max($this->discountCode->repeatCount, 1));
			$discount = $numberDiscounts * (float)$this->discountCode->discount;

			if ($discount > $discountableAmount)
				{
				$discount = $discountableAmount;
				}
			}

		return $discount;
		}

	/**
	 * @param array<int,array<string,int|string|float>>|\App\Record\InvoiceItem $item
	 * @param array<string> $validItemNumbers
	 */
	private function isEligible(array|\App\Record\InvoiceItem $item, array $validItemNumbers) : bool
		{
		if (! \count($validItemNumbers))
			{
			return true;
			}

		$itemNumber = $item['storeItemId'] . '-' . $item['storeItemDetailId'];

		foreach ($validItemNumbers as $validItemNumber)
			{
			if ($itemNumber == $validItemNumber)
				{
				return true;
				}

			if ("{$item['storeItemId']}" == $validItemNumber)
				{
				return true;
				}
			}

		return false;
		}
	}
