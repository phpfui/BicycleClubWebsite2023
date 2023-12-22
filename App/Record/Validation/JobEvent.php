<?php

namespace App\Record\Validation;

class JobEvent extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'cutoffDate' => ['date', 'lte_field:date'],
		'date' => ['required', 'date', 'gte_field:cutoffDate'],
		'email' => ['maxlength', 'email'],
		'name' => ['maxlength'],
		'organizer' => ['integer'],
	];

	public function __construct(\App\Record\JobEvent $record)
		{
		parent::__construct($record);
		}
	}
