<?php

namespace App\Record\Validation;

class InvoiceItem extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'description' => ['maxlength'],
		'detailLine' => ['maxlength'],
		'price' => ['number'],
		'quantity' => ['integer'],
		'shipping' => ['number'],
		'tax' => ['number'],
		'title' => ['maxlength'],
		'type' => ['integer'],
	];

	public function __construct(\App\Record\InvoiceItem $record)
		{
		parent::__construct($record);
		}
	}
