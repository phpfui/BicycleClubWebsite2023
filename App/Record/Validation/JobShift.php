<?php

namespace App\Record\Validation;

class JobShift extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'endTime' => ['required', 'time'],
		'jobId' => ['integer'],
		'needed' => ['integer'],
		'startTime' => ['required', 'time'],
	];

	public function __construct(\App\Record\JobShift $record)
		{
		parent::__construct($record);
		}
	}
