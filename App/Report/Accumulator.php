<?php

namespace App\Report;

class Accumulator
	{
	public string $itemName;

	public int $numberSold;

	public float $totalPrice;

	public float $totalShipping;

	public float $totalTax;

	public $type;

	public function __construct()
		{
		$this->itemName = '';
		$this->type = 0;
		$this->totalTax = 0.0;
		$this->numberSold = 0;
		$this->totalPrice = 0.0;
		$this->totalShipping = 0.0;
		}

	public function increment($invoiceItem) : void
		{
		$this->itemName = $invoiceItem['title'];
		$this->type = $invoiceItem['type'];
		$this->numberSold += $invoiceItem['quantity'];
		$this->totalPrice += $invoiceItem['price'] * $invoiceItem['quantity'];
		$this->totalShipping += $invoiceItem['shipping'] * $invoiceItem['quantity'];
		$this->totalTax += $invoiceItem['tax'];
		}
	}
