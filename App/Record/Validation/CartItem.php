<?php

namespace App\Record\Validation;

class CartItem extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'dateAdded' => ['required', 'date'],
		'discountCodeId' => ['integer'],
		'memberId' => ['required', 'integer'],
		'optionsSelected' => ['maxlength'],
		'quantity' => ['integer'],
		'storeItemDetailId' => ['integer'],
		'storeItemId' => ['integer'],
		'type' => ['integer'],
	];

	public function __construct(\App\Record\CartItem $record)
		{
		parent::__construct($record);
		}
	}
