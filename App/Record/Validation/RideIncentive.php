<?php

namespace App\Record\Validation;

class RideIncentive extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'incentiveId' => ['integer'],
	];

	public function __construct(\App\Record\RideIncentive $record)
		{
		parent::__construct($record);
		}
	}
