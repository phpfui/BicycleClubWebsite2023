<?php

namespace App\Record\Validation;

class SigninSheetRide extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
	];

	public function __construct(\App\Record\SigninSheetRide $record)
		{
		parent::__construct($record);
		}
	}
