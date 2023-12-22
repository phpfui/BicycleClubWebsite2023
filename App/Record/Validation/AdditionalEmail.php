<?php

namespace App\Record\Validation;

class AdditionalEmail extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'email' => ['required', 'maxlength', 'email'],
		'memberId' => ['required', 'integer'],
		'verified' => ['required', 'integer'],
	];

	public function __construct(\App\Record\AdditionalEmail $record)
		{
		parent::__construct($record);
		}
	}
