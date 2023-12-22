<?php

namespace App\Record\Validation;

class Setting extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'value' => ['maxlength'],
	];

	public function __construct(\App\Record\Setting $record)
		{
		parent::__construct($record);
		}
	}
