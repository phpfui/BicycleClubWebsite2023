<?php

namespace App\Record\Validation;

class VolunteerPollAnswer extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'answer' => ['maxlength'],
		'volunteerPollId' => ['integer'],
	];

	public function __construct(\App\Record\VolunteerPollAnswer $record)
		{
		parent::__construct($record);
		}
	}
