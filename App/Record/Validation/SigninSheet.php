<?php

namespace App\Record\Validation;

class SigninSheet extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'dateAdded' => ['required', 'date'],
		'ext' => ['maxlength'],
		'memberId' => ['required', 'integer'],
		'pending' => ['integer'],
		'pointsAwarded' => ['required', 'integer'],
	];

	public function __construct(\App\Record\SigninSheet $record)
		{
		parent::__construct($record);
		}
	}
