<?php

namespace App\Model;

class Cart
	{
	private bool $computed = false;

	private int $count = 0;

	private readonly \App\Model\Customer $customerModel;

	private float $discount = 0.0;

	private ?\App\Record\DiscountCode $discountCode;

	/** @var array<array<string, string>> */
	private array $items = [];

	private int $memberId = 0;

	private float $payableByPoints = 0.0;

	private float $shipping = 0.0;

	private bool $showMenus = true;

	private float $tax = 0.0;

	private float $total = 0.0;

	private int $volunteerPoints = 0;

	public function __construct()
		{
		$this->customerModel = new \App\Model\Customer();
		$this->memberId = $this->customerModel->getNumber();
		$this->discountCode = new \App\Record\DiscountCode();
		$this->zero();
		}

	/**
	 * @param array<string,string|array<string>> $request
	 */
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
				$cartItem->type = \App\Enum\Store\Type::ORDER;
				}

			if (isset($request['storeItemDetailId']))
				{
				$cartItem->storeItemDetailId = (int)$request['storeItemDetailId'];
				$cartItem->type = \App\Enum\Store\Type::STORE;
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

	/**
	 * @param array<string,mixed> $rider
	 */
	public function addGaRider(array $rider) : int
		{
		// storeItemId = gaEventId;
		// storeItemDetailId = gaRiderId
		$rider['pending'] = 1;

		foreach ($rider as $key => $value)
			{
			if ('email' != $key && ! \is_array($value))
				{
				$rider[$key] = \ucwords($value ?? '');
				}
			}
		$gaRider = new \App\Record\GaRider();
		$gaRider->setFrom($rider);
		$errors = $gaRider->validate();

		if ($errors)
			{
			\App\Model\Session::setFlash('alert', $errors);

			return 0;
			}

		$gaRiderId = $gaRider->insert();

		foreach ($rider['gaOption'] ?? [] as $gaOptionId => $gaSelectionId)
			{
			$gaRiderSelection = new \App\Record\GaRiderSelection();
			$gaRiderSelection->gaRiderId = $gaRiderId;
			$gaRiderSelection->gaSelectionId = $gaSelectionId;
			$gaRiderSelection->gaOptionId = $gaOptionId;
			$gaRiderSelection->insert();
			}

		if (! $this->memberId)
			{
			$this->memberId = $this->customerModel->getNewNumber();
			}

		$cartItem = new \App\Record\CartItem();
		$cartItem->setFrom($rider);
		$cartItem->storeItemDetailId = $gaRiderId;
		$cartItem->storeItemId = (int)$rider['gaEventId'];
		$cartItem->memberId = $this->memberId;
		$cartItem->type = \App\Enum\Store\Type::GENERAL_ADMISSION;
		$cartItem->quantity = 1;
		$cartItem->discountCodeId = 0;
		$cartItem->dateAdded = \App\Tools\Date::todayString();

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
				if (\App\Enum\Store\Type::STORE->value == $cartItem['type']) // type 0, has inventory
					{
					$key = ['storeItemId' => $cartItem['storeItemId'],
						'storeItemDetailId' => $cartItem['storeItemDetailId'], ];
					$storeItemDetail = new \App\Record\StoreItemDetail($key);

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

	public function compute(int $volunteerPoints = 0) : void
		{
		$discountCode = null;

		if (! $this->computed)
			{
			$this->volunteerPoints = $volunteerPoints;
			$this->computed = true;
			$this->zero();

			$items = $this->getItems();

			foreach ($items as $cartItem)
				{
				if (! empty($cartItem['quantity']))
					{
					$value = (float)($cartItem['price'] * $cartItem['quantity']);

					if ($cartItem['payByPoints'])
						{
						$this->payableByPoints += $value;
						}

					if (! $cartItem['noShipping'])
						{
						$this->shipping += (float)$cartItem['shipping'];
						}
					$this->count += $cartItem['quantity'];
					$this->total += $value;

					if (\App\Enum\Store\Type::GENERAL_ADMISSION->value == $cartItem['type'])
						{
						$rider = new \App\Record\GaRider($cartItem['storeItemDetailId']);

						foreach ($rider->optionsSelected as $option)
							{
							$this->total += $option->price + $option->additionalPrice;
							}
						}
					}
				elseif (! empty($cartItem['discountCodeId'])) // a discount code
					{
					$discountCode = new \App\Record\DiscountCode($cartItem['discountCodeId']);
					}
				}
			$this->discountCode = $discountCode;
			$discountCodeModel = new \App\Model\DiscountCode($discountCode);
			$this->discount = $discountCodeModel->computeDiscount($items, $this->getCashAmount());
			}
		}

	public function delete(\App\Record\CartItem $cartItem) : bool
		{
		if (\App\Enum\Store\Type::EVENT == $cartItem->type)
			{
			}
		elseif (\App\Enum\Store\Type::GENERAL_ADMISSION == $cartItem->type)
			{
			$gaRider = new \App\Record\GaRider();
			$gaRider->gaRiderId = $cartItem->storeItemDetailId;
			$gaRider->delete();
			}

		return $cartItem->delete();
		}

	public function getCashAmount() : float
		{
		$appliedPoints = 0;

		if ($this->volunteerPoints)
			{
			$appliedPoints = \min($this->payableByPoints, $this->volunteerPoints);
			}
		$total = $this->total - $appliedPoints;

		if ($total < 0.0)
			{
			$total = 0.0;
			}

		return $total;
		}

	public function getCount() : int
		{
		return $this->count;
		}

	public function getCustomerNumber() : int
		{
		return $this->memberId;
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
		$appliedPoints = 0;

		if ($this->volunteerPoints)
			{
			$appliedPoints = \min($this->payableByPoints, $this->volunteerPoints);
			}
		$total = $this->total + $this->shipping + $this->tax - $this->discount - $appliedPoints;

		if ($total < 0.0)
			{
			$total = 0.0;
			}

		return $total;
		}

	/**
	 * @return array<array<string, mixed>>
	 */
	public function getItems() : array
		{
		if (empty($this->items))
			{
			$this->items = \App\Table\CartItem::getCartFor($this->memberId);
			$gaModel = new \App\Model\GeneralAdmission();
			$this->tax = 0.0;
			$taxCalculator = new \App\Model\TaxCalculator();

			foreach ($this->items as &$cartItem)
				{
				$cartItemRecord = new \App\Record\CartItem();
				$cartItemRecord->setFrom($cartItem);
				$tax = $taxCalculator->compute($cartItemRecord->toArray(), 0.0);
				$this->tax += $tax;
				$cartItem['tax'] = $tax;

				$pickupTotal = $shipping = $items = $points = $noShipping = 0;

				if (\App\Enum\Store\Type::GENERAL_ADMISSION->value == $cartItem['type'])
					{
					// storeItemId = gaEventId;
					// storeItemDetailId = gaRiderId
					$event = new \App\Record\GaEvent($cartItem['storeItemId']);

					if (! $event->allowShopping)
						{
						$this->showMenus = false;
						}
					$rider = new \App\Record\GaRider($cartItem['storeItemDetailId']);
					$cartItem['title'] = $event->title;
					$cartItem['description'] = $event->description;
					$cartItem['detailLine'] = $rider->firstName . ' ' . $rider->lastName;
					$cartItem['price'] = $gaModel->getPrice($event, $rider);
					$cartItem['payByPoints'] = 0;
					$cartItem['clothing'] = 0;
					$cartItem['quantity'] = 1;
					$cartItem['noShipping'] = 1;
					$items = 1;
					}
				elseif (\App\Enum\Store\Type::DISCOUNT_CODE->value == $cartItem['type'])
					{
					// nothing to do here!
					}
				elseif (\in_array($cartItem['type'], [\App\Enum\Store\Type::STORE->value, \App\Enum\Store\Type::MEMBERSHIP->value, \App\Enum\Store\Type::ORDER->value]))
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
						$items = $cartItem['quantity'];
						}
					}
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

	public function getShowMenus() : bool
		{
		return $this->showMenus;
		}

	public function getTax() : float
		{
		return $this->tax;
		}

	public function getTotal() : float
		{
		return $this->total;
		}

	public function getVolunteerPoints() : int
		{
		return $this->volunteerPoints;
		}

	public function setMemberId(int $memberId) : self
		{
		$this->memberId = $memberId;

		return $this;
		}

	/**
	 * @param array<string,string> $quantities
	 */
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
		$cartItemTable = new \App\Table\CartItem();
		$cartItemTable->deleteDiscountForMember($this->memberId);

		if (! $discount->empty() && $this->validateDiscountCode($discount, $this->memberId))
			{
			$cartItem = new \App\Record\CartItem();
			$cartItem->memberId = $this->memberId;
			$cartItem->discountCode = $discount;
			$cartItem->type = \App\Enum\Store\Type::DISCOUNT_CODE;
			$cartItem->dateAdded = \App\Tools\Date::todayString();
			$cartItem->insert();

			return true;
			}

		return false;
		}

	private function validateDiscountCode(\App\Record\DiscountCode $discount, int $customerNumber) : bool
		{
		if ($discount->expirationDate && \App\Tools\Date::todayString() > $discount->expirationDate)
			{
			return false;
			}

		if ($discount->startDate && \App\Tools\Date::todayString() < $discount->startDate)
			{
			return false;
			}

		if ($discount->validItemNumbers)
			{
			if (! \App\Table\CartItem::getItemCountForMember($discount->validItemNumbers, $customerNumber))
				{
				return false;
				}
			}

		return ! ($discount->maximumUses && $discount->timesUsed >= $discount->maximumUses);
		}

	private function zero() : void
		{
		$this->count = 0;
		$this->payableByPoints = $this->discount = $this->tax = $this->total = $this->shipping = 0.0;
		}
	}
