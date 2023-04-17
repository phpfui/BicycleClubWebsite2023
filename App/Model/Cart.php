<?php

namespace App\Model;

class Cart
	{
	final public const TYPE_DISCOUNT_CODE = 2;

	final public const TYPE_EVENT = 3;

	final public const TYPE_GA = 1;

	final public const TYPE_MEMBERSHIP = 4;

	final public const TYPE_STORE = 0;

	final public const TYPE_ORDER = 5;

	private int $availablePoints = 0;

	private int $volunteerPoints = 0;

	private bool $computed = false;

	private int $count = 0;

	private float $discount = 0.0;

	private \App\Record\DiscountCode $discountCode;

	private readonly \App\Model\Customer $customerModel;

	private array $items = [];

	private int $memberId = 0;

	private float $payableByPoints = 0.0;

	private float $shipping = 0.0;

	private float $tax = 0.0;

	private float $taxRate = 0.0;

	private float $total = 0.0;

	public function __construct()
		{
		$this->customerModel = new \App\Model\Customer();
		$this->memberId = $this->customerModel->getNumber();
		$this->discountCode = new \App\Record\DiscountCode();
		$this->zero();
		}

	public function addFromStore(array $request) : void
		{
		if (! $this->memberId)
			{
			$this->memberId = $this->customerModel->getNewNumber();
			}
		$options = $comma = '';

		foreach ($request['storeOption'] ?? [] as $name => $value)
			{
			$options .= $comma . $name . ':' . $value;
			$comma = ',';
			}

		if ($options)
			{
			$request['optionsSelected'] = $options;
			}
		$request['memberId'] = $this->memberId;

		$keyFields = ['storeItemId', 'storeItemDetailId', 'memberId', 'optionsSelected'];
		$cartItemTable = new \App\Table\CartItem();
		$condition = new \PHPFUI\ORM\Condition();

		foreach ($keyFields as $field)
			{
			if (isset($request[$field]))
				{
				$condition->and(new \PHPFUI\ORM\Condition($field, $request[$field]));
				}
			}
		$cartItemTable->setWhere($condition);
		$cartItem = $cartItemTable->getRecordCursor()->current();

		// we only add one thing at a time to the cart here, increment quantity if found
		if (! $cartItem->empty())
			{
			$cartItem->quantity += 1;
			$cartItem->dateAdded = \App\Tools\Date::todayString();
			$cartItem->update();
			}
		else
			{
			$cartItem->storeItemId = (int)$request['storeItemId'];
			$cartItem->dateAdded = \App\Tools\Date::todayString();
			$cartItem->memberId = $this->memberId;
			$cartItem->quantity = 1;

			if (isset($request['optionsSelected']))
				{
				$cartItem->optionsSelected = $request['optionsSelected'];
				$cartItem->type = \App\Model\Cart::TYPE_ORDER;
				}

			if (isset($request['storeItemDetailId']))
				{
				$cartItem->storeItemDetailId = (int)$request['storeItemDetailId'];
				$cartItem->type = \App\Model\Cart::TYPE_STORE;
				}

			if (isset($request['quantity']))
				{
				$cartItem->quantity = $request['quantity'];
				}
			else
				{
				$cartItem->quantity = 1;
				}
			$cartItem->insert();
			}
		}

	public function addGaRider(array $rider) : int
		{
		// storeItemId = gaEventId;
		// storeItemDetailId = gaRiderId
		$rider['pending'] = 1;

		foreach ($rider as $key => $value)
			{
			if ('email' != $key)
				{
				$rider[$key] = \ucwords($value ?? '');
				}
			}
		$gaRider = new \App\Record\GaRider();
		$gaRider->setFrom($rider);
		$gaRiderId = $gaRider->insert();
		$rider['gaRiderId'] = $rider['storeItemDetailId'] = $gaRiderId;
		$rider['storeItemId'] = $rider['gaEventId'];
		$rider['type'] = self::TYPE_GA;
		$rider['quantity'] = 1;
		$rider['discountCodeId'] = 0;
		$rider['dateAdded'] = \App\Tools\Date::todayString();

		if (! $this->memberId)
			{
			$this->memberId = $this->customerModel->getNewNumber();
			}
		$rider['memberId'] = $this->memberId;

		$cartItem = new \App\Record\CartItem();
		$cartItem->setFrom($rider);
		$cartItem->insert();

		return $gaRiderId;
		}

	public function check() : bool
		{
		$returnValue = true;
		$cartItems = [];

		if ($this->memberId)
			{
			$cartItems = $this->getItems();

			foreach ($cartItems as $cartItem)
				{
				if (\App\Model\Cart::TYPE_STORE == $cartItem['type']) // type 0, has inventory
					{
					$key = ['storeItemId' => $cartItem['storeItemId'],
						'storeItemDetailId' => $cartItem['storeItemDetailId'], ];
					$storeItemDetail = new \App\Record\StoreItemDetail();
					$storeItemDetail->read($key);

					if ($storeItemDetail->empty())
						{
						$returnValue = false;
						$cartItemRecord = new \App\Record\CartItem($cartItem['cartItemId']);
						$cartItemRecord->delete();
						}
					elseif ($storeItemDetail->quantity < $cartItem['quantity'])
						{
						$returnValue = false;

						$cartItemRecord = new \App\Record\CartItem($cartItem['cartItemId']);

						if ($storeItemDetail->quantity)
							{
							$cartItemRecord->quantity = $storeItemDetail->quantity;
							$cartItemRecord->update();
							}
						else
							{
							$cartItemRecord->delete();
							}
						}
					}
				}
			}

		if (! $returnValue)
			{
			$this->items = [];
			}

		return $returnValue && $cartItems;
		}

	public function compute(float $taxRate, int $availablePoints = 0) : void
		{
		$this->taxRate = $taxRate;

		if (! $this->computed)
			{
			$this->volunteerPoints = $availablePoints;
			$this->availablePoints = $availablePoints;
			$this->computed = true;
			$this->zero();
			$itemCount = [];

			foreach ($this->getItems() as $cartItem)
				{
				if (! empty($cartItem['quantity']))
					{
					$itemCount[$cartItem['storeItemId'] . '-' . ($cartItem['storeItemDetailId'] ?? 0)] = $cartItem['quantity'];
					$value = $taxableValue = (float)($cartItem['price'] * $cartItem['quantity']);

					if ($cartItem['payByPoints'])
						{
						$this->payableByPoints += $value;

						if ($this->availablePoints > 0)
							{
							if ($this->availablePoints >= $taxableValue)
								{
								$this->availablePoints -= (int)$taxableValue;
								$taxableValue = 0.0;
								}
							else
								{
								$taxableValue -= (float)$this->availablePoints;
								$this->availablePoints = 0;
								}
							}
						}

					if ($cartItem['taxableTotal'])
						{
						$this->tax += $taxableValue * $this->taxRate / 100.00 + (float)$cartItem['shipping'] * $this->taxRate / 100.00;
						}

					if ($cartItem['clothingTotal'])
						{
						$localTaxRate = $this->taxRate - 4.0;
						$localTaxRate = \max($localTaxRate, 0.0);
						$this->tax += (float)$cartItem['shipping'] * $this->taxRate / 100.00;
						$this->tax += $taxableValue * $localTaxRate / 100.00;
						}

					if (! $cartItem['noShipping'])
						{
						$this->shipping += (float)$cartItem['shipping'];
						}
					$this->count += $cartItem['quantity'];
					$this->total += $value;
					}
				elseif (! empty($cartItem['discountCodeId'])) // a discount code
					{
					$this->discount = $this->computeDiscount(new \App\Record\DiscountCode($cartItem['discountCodeId']), $itemCount);
					}
				}
			}
		}

	public function getCount() : int
		{
		return $this->count;
		}

	public function getCustomerNumber() : int
		{
		return $this->memberId;
		}

	public function getVolunteerPoints() : int
		{
		return $this->volunteerPoints;
		}

	public function getDiscount() : float
		{
		return $this->discount;
		}

	public function getDiscountCode() : \App\Record\DiscountCode
		{
		return $this->discountCode;
		}

	public function getGrandTotal() : float
		{
		$appliedPoints = \min($this->payableByPoints, $this->volunteerPoints);
		$total = $this->total + $this->shipping + $this->tax - $this->discount - $appliedPoints;

		if ($total < 0.0)
			{
			$total = 0.0;
			}

		return $total;
		}

	public function getItems() : array
		{
		if (empty($this->items))
			{
			$this->items = \App\Table\CartItem::getCartFor($this->memberId);
			$gaModel = new \App\Model\GeneralAdmission();
			$zipTax = new \App\Table\Ziptax();

			foreach ($this->items as &$cartItem)
				{
				$taxableTotal = $nonTaxableTotal = $pickupTotal = $shipping = $items = $clothingTotal = $points = $noShipping = 0;

				if (\App\Model\Cart::TYPE_GA == $cartItem['type'])
					{
					// storeItemId = gaEventId;
					// storeItemDetailId = gaRiderId
					$event = new \App\Record\GaEvent($cartItem['storeItemId']);
					$rider = new \App\Record\GaRider($cartItem['storeItemDetailId']);
					$nonTaxableTotal = $gaModel->getPrice($event, $rider);
					$cartItem['title'] = $event->title;
					$cartItem['description'] = $event->description;
					$cartItem['detailLine'] = $rider->firstName . ' ' . $rider->lastName;
					$cartItem['price'] = $nonTaxableTotal;
					$cartItem['taxable'] = 0;
					$cartItem['payByPoints'] = 0;
					$cartItem['clothing'] = 0;
					$cartItem['quantity'] = 1;
					$cartItem['noShipping'] = 1;
					$items = 1;
					}
				elseif (\App\Model\Cart::TYPE_DISCOUNT_CODE == $cartItem['type'])
					{
					// nothing to do here!
					}
				elseif (\in_array($cartItem['type'], [\App\Model\Cart::TYPE_STORE, \App\Model\Cart::TYPE_MEMBERSHIP, \App\Model\Cart::TYPE_ORDER]))
					{
					$storeItem = new \App\Record\StoreItem($cartItem['storeItemId']);

					if (! $storeItem->empty())
						{
						$value = $storeItem->price * $cartItem['quantity'];

						if ($storeItem->payByPoints)
							{
							$points += $value;
							}

						if (! $storeItem->noShipping)
							{
							$shipping = $storeItem->shipping * (int)$cartItem['quantity'];
							}

						if ($storeItem->taxable)
							{
							if ((int)($storeItem->pickupZip))
								{
								$pickupTotal += $zipTax->getTaxRateForZip($storeItem->pickupZip);
								}
							elseif ($storeItem->clothing)
								{
								$clothingTotal += $value;
								}
							else
								{
								$taxableTotal += $value + $shipping;
								}
							}
						else
							{
							$nonTaxableTotal += $value;
							}
						$items = $cartItem['quantity'];
						}
					}
				$cartItem['taxableTotal'] = $taxableTotal;
				$cartItem['nonTaxableTotal'] = $nonTaxableTotal;
				$cartItem['clothingTotal'] = $clothingTotal;
				$cartItem['pickupTotal'] = $pickupTotal;
				$cartItem['shipping'] = $shipping;
				$cartItem['points'] = $points;
				$cartItem['items'] = $items;
				}
			}

		return $this->items;
		}

	public function getPayableByPoints() : float
		{
		return $this->payableByPoints;
		}

	public function getShipping() : float
		{
		return $this->shipping;
		}

	public function getTax() : float
		{
		return $this->tax;
		}

	public function getTaxRate() : float
		{
		return $this->taxRate;
		}

	public function getTotal() : float
		{
		return $this->total;
		}

	public function updateCartQuantities(array $quantities) : void
		{
		foreach ($quantities as $key => $quantity)
			{
			$quantity = (int)$quantity;

			$cartItem = new \App\Record\CartItem($key);
			$storeItemDetail = $cartItem->StoreItemDetail->current();

			if (\count($cartItem->StoreItemDetail) && isset($storeItemDetail->quantity))
				{
				if ($storeItemDetail->quantity < $quantity)
					{
					$quantity = $storeItemDetail->quantity;
					}
				}

			$cartItem->quantity = $quantity;
			$cartItem->update();
			}
		}

	public function updateDiscount(string $discountCode) : bool
		{
		$discount = new \App\Record\DiscountCode(['discountCode' => $discountCode]);
		$discountCodeId = ! $discount->empty() ? $this->validateDiscountCode($discount, $this->memberId) : 0;
		$cartItemTable = new \App\Table\CartItem();
		$cartItemTable->deleteDiscountForMember($this->memberId);

		if ($discount->discountCodeId)
			{
			$cartItem = new \App\Record\CartItem();
			$cartItem->memberId = $this->memberId;
			$cartItem->discountCode = $discount;
			$cartItem->type = 2;
			$cartItem->dateAdded = \App\Tools\Date::todayString();
			$cartItem->insert();

			return true;
			}

		return false;
		}

	private function computeDiscount(\App\Record\DiscountCode $discountCode, array $itemCount) : float
		{
		$discount = 0.0;
		$this->discountCode = $discountCode;

		if (! $discountCode->empty())
			{
			$eligibleCount = 1;
			$eligibleItemNumbers = [];

			if ($discountCode->validItemNumbers)
				{
				$eligibleItemNumbers = \explode(',', $discountCode->validItemNumbers);
				$eligibleCount = 0;
				}

			foreach ($eligibleItemNumbers as $eligibleItemNumber)
				{
				foreach ($itemCount as $itemNumber => $count)
					{
					$eligibleParts = \explode('-', $eligibleItemNumber);
					/** @noinspection PhpUnusedLocalVariableInspection */
					[$itemId, $detailId] = \explode('-', $itemNumber);

					if ($itemNumber == $eligibleItemNumber || $itemId == $eligibleItemNumber || ($itemId == $eligibleParts[0] && 1 == \count($eligibleParts)))
						{
						$eligibleCount += $count;
						}
					}
				}
			$numberDiscounts = \min($eligibleCount, \max($discountCode->repeatCount, 1));
			$discount = $numberDiscounts * (float)$discountCode->discount;
			}

		return $discount;
		}

	private function validateDiscountCode(\App\Record\DiscountCode $discount, $customerNumber) : int
		{
		if ($discount->expirationDate && \App\Tools\Date::todayString() > $discount->expirationDate)
			{
			return 0;
			}

		if ($discount->startDate && \App\Tools\Date::todayString() < $discount->startDate)
			{
			return 0;
			}

		if ($discount->validItemNumbers)
			{
			if (! \App\Table\CartItem::getItemCountForMember($discount->validItemNumbers, $customerNumber))
				{
				return 0;
				}
			}

		if ($discount->maximumUses)
			{
			if (\App\Table\Invoice::getDiscountCodeTimesUsed($discount->discountCodeId) >= $discount->maximumUses)
				{
				return 0;
				}
			}

		return $discount->discountCodeId;
		}

	private function zero() : void
		{
		$this->count = 0;
		$this->payableByPoints = $this->discount = $this->tax = $this->total = $this->shipping = 0.0;
		}
	}
