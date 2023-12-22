<?php

namespace App\Record\Validation;

class DiscountCode extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'description' => ['maxlength'],
		'discount' => ['number', 'required'],
		'discountCode' => ['maxlength', 'required'],
		'expirationDate' => ['required', 'date', 'gte_field:startDate'],
		'maximumUses' => ['integer'],
		'repeatCount' => ['integer'],
		'startDate' => ['required', 'date', 'lte_field:expirationDate'],
		'validItemNumbers' => ['maxlength'],
	];

	public function __construct(\App\Record\DiscountCode $record)
		{
		parent::__construct($record);
		}
	}
