<?php

namespace App\Record\Validation;

class VolunteerPoll extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'jobEventId' => ['integer'],
		'question' => ['maxlength'],
	];

	public function __construct(\App\Record\VolunteerPoll $record)
		{
		parent::__construct($record);
		}
	}
