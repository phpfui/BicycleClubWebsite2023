<?php

namespace App\Model;

class TaxCalculator
	{
	private \NXP\MathExecutor $executor;

	public function __construct()
		{
		$this->executor = new \NXP\MathExecutor();
		}

	public function compute(\App\Record\CartItem $cartItem) : float
		{
		$this->executor->setVars($cartItem->toArray());
		$storeItem = $cartItem->storeItem;

		if (! $storeItem->taxable)
			{
			return 0.0;
			}
		$this->executor->setVars($storeItem->toArray(), false);
//		$this->executor->setVars($cartItem->StoreItemDetail->current()->toArray(), false);
		$member = $cartItem->member;
		$this->executor->setVars($member->toArray(), false);
		$membership = $member->membership;
		$this->executor->setVars($membership->toArray(), false);
		$zipTaxTable = new \App\Table\Ziptax();
		$this->executor->setVar('taxRate', $zipTaxTable->getTaxRateForZip($membership->zip));
		$settingTable = new \App\Table\Setting();

		return $this->executor->execute($settingTable->value('salesTaxFormula'));
		}
	}
