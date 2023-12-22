<?php

namespace App\Record\Validation;

class Ziptax extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'zip_code' => ['maxlength', 'required'],
		'zip_tax_rate' => ['number', 'required'],
		'zipcounty' => ['maxlength'],
		'zipstate' => ['maxlength'],
	];

	public function __construct(\App\Record\Ziptax $record)
		{
		parent::__construct($record);
		}
	}
