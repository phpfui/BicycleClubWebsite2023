<?php

namespace App\Record\Validation;

class VolunteerPollResponse extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'answer' => ['integer'],
	];

	public function __construct(\App\Record\VolunteerPollResponse $record)
		{
		parent::__construct($record);
		}
	}
