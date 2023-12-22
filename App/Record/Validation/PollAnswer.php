<?php

namespace App\Record\Validation;

class PollAnswer extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'answer' => ['maxlength'],
	];

	public function __construct(\App\Record\PollAnswer $record)
		{
		parent::__construct($record);
		}
	}
