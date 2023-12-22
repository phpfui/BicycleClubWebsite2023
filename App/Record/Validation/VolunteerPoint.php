<?php

namespace App\Record\Validation;

class VolunteerPoint extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'pointsAwarded' => ['required', 'integer'],
	];

	public function __construct(\App\Record\VolunteerPoint $record)
		{
		parent::__construct($record);
		}
	}
