<?php

namespace App\Record\Validation;

class StoreOrder extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'added' => ['required', 'datetime'],
		'invoiceId' => ['required', 'integer'],
		'memberId' => ['required', 'integer'],
		'optionsSelected' => ['required', 'maxlength'],
		'quantity' => ['required', 'integer'],
		'storeItemId' => ['required', 'integer'],
		'updated' => ['required', 'datetime'],
	];

	public function __construct(\App\Record\StoreOrder $record)
		{
		parent::__construct($record);
		}
	}
