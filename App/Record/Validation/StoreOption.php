<?php

namespace App\Record\Validation;

class StoreOption extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'optionName' => ['required', 'maxlength'],
		'optionValues' => ['required', 'maxlength'],
	];

	public function __construct(\App\Record\StoreOption $record)
		{
		parent::__construct($record);
		}
	}
