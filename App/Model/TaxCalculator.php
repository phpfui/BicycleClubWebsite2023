<?php

namespace App\Model;

class TaxCalculator
	{
	private \NXP\MathExecutor $executor;

	public function __construct()
		{
		$this->executor = new \NXP\MathExecutor();
		}

	/**
	 * @param array<string,mixed> $cartItem
	 */
	public function compute(array $cartItem, float $volunteerPoints) : float
		{
		$storeItem = new \App\Record\StoreItem($cartItem['storeItemId']);

		if (! $storeItem->taxable)
			{
			return 0.0;
			}
		$this->executor->setVars($cartItem);
		$this->executor->setVar('volunteerPoints', $volunteerPoints);
		$this->executor->setVars($storeItem->toArray(), false);
		$member = new \App\Record\Member($cartItem['memberId']);
		$this->executor->setVars($member->toArray(), false);
		$membership = $member->membership;
		$this->executor->setVars($membership->toArray(), false);
		$zipTaxTable = new \App\Table\Ziptax();
		$this->executor->setVar('taxRate', $zipTaxTable->getTaxRateForZip($membership->zip ?? ''));
		$settingTable = new \App\Table\Setting();

		$tax = $this->executor->execute($settingTable->value('salesTaxFormula'));

		if ($tax < 0.0)
			{
			$tax = 0;
			}

		return $tax;
		}
	}
