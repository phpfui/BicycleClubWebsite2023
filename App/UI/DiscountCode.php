<?php

namespace App\UI;

class DiscountCode
	{
	public function __construct(private readonly \App\Record\DiscountCode $currentDiscountCode, private readonly string $badDiscountCode)
		{
		}

	public function __toString() : string
		{
		$fieldSet = new \PHPFUI\FieldSet('Discount Code');
		$row = new \PHPFUI\GridX();
		$cola = new \PHPFUI\Cell(6);
		$colb = new \PHPFUI\Cell(6);

		if ($this->currentDiscountCode->empty())
			{
			if ($this->badDiscountCode)
				{
				$cola->add('<b>' . $this->badDiscountCode . '</b> is not a valid discount code.');
				}
			$cola->add(new \PHPFUI\Input\Text('discountCode'));
			$colb->add(new \PHPFUI\Submit('Apply'));
			}
		else
			{
			$cola->add(new \PHPFUI\Header($this->currentDiscountCode->discountCode, 4));
			$cola->add(new \PHPFUI\Input\Hidden('discountCode', $this->currentDiscountCode->discountCode));
			$cola->add($this->currentDiscountCode->description);
			$colb->add(new \PHPFUI\Submit('Remove'));
			}
		$row->add($cola);
		$row->add($colb);
		$fieldSet->add($row);

		return $fieldSet;
		}
	}
