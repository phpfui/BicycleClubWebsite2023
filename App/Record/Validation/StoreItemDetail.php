<?php

namespace App\Record\Validation;

class StoreItemDetail extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'detailLine' => ['maxlength'],
		'quantity' => ['integer'],
	];

	public function __construct(\App\Record\StoreItemDetail $record)
		{
		parent::__construct($record);
		}
	}
