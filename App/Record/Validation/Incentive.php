<?php

namespace App\Record\Validation;

class Incentive extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'description' => ['maxlength'],
		'notRide' => ['integer'],
		'points' => ['integer'],
	];

	public function __construct(\App\Record\Incentive $record)
		{
		parent::__construct($record);
		}
	}
