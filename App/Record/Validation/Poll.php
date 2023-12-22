<?php

namespace App\Record\Validation;

class Poll extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'emailConfirmation' => ['integer'],
		'endDate' => ['required', 'date', 'gte_field:startDate'],
		'memberId' => ['integer'],
		'membershipOnly' => ['integer'],
		'question' => ['maxlength'],
		'required' => ['integer'],
		'startDate' => ['required', 'date', 'lte_field:endDate'],
	];

	public function __construct(\App\Record\Poll $record)
		{
		parent::__construct($record);
		}
	}
