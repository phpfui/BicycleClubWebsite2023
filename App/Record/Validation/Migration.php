<?php

namespace App\Record\Validation;

class Migration extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'ran' => ['required', 'datetime'],
	];

	public function __construct(\App\Record\Migration $record)
		{
		parent::__construct($record);
		}
	}
