<?php

namespace App\Record\Validation;

class PollResponse extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'answer' => ['integer'],
	];

	public function __construct(\App\Record\PollResponse $record)
		{
		parent::__construct($record);
		}
	}
