<?php

namespace App\Record\Validation;

class Job extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'date' => ['required', 'date'],
		'description' => ['maxlength'],
		'jobEventId' => ['integer'],
		'location' => ['maxlength'],
		'organizer' => ['integer'],
		'title' => ['maxlength', 'required'],
	];

	public function __construct(\App\Record\Job $record)
		{
		parent::__construct($record);
		}
	}
