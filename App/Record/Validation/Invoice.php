<?php

namespace App\Record\Validation;

class Invoice extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'discount' => ['number'],
		'discountCodeId' => ['integer'],
		'errors' => ['maxlength'],
		'fullfillmentDate' => ['date'],
		'instructions' => ['maxlength'],
		'memberId' => ['required', 'integer'],
		'orderDate' => ['required', 'date'],
		'paidByCheck' => ['integer'],
		'paymentDate' => ['date'],
		'paypalPaid' => ['number'],
		'paypaltx' => ['maxlength'],
		'pointsUsed' => ['integer'],
		'totalPrice' => ['number'],
		'totalShipping' => ['number'],
		'totalTax' => ['number'],
	];

	public function __construct(\App\Record\Invoice $record)
		{
		parent::__construct($record);
		}
	}
