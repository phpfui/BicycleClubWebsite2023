<?php

namespace App\Record\Validation;

class StoreItem extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'active' => ['required', 'integer'],
		'clothing' => ['integer'],
		'cut' => ['maxlength'],
		'description' => ['maxlength'],
		'noShipping' => ['integer'],
		'parent' => ['integer'],
		'payByPoints' => ['integer'],
		'pickupZip' => ['maxlength'],
		'pointsOnly' => ['required', 'integer'],
		'price' => ['number'],
		'shipping' => ['number'],
		'taxable' => ['integer'],
		'title' => ['maxlength'],
		'type' => ['required', 'integer'],
	];

	public function __construct(\App\Record\StoreItem $record)
		{
		parent::__construct($record);
		}
	}
