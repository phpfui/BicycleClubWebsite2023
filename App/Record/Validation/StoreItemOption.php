<?php

namespace App\Record\Validation;

class StoreItemOption extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'sequence' => ['required', 'integer'],
	];

	public function __construct(\App\Record\StoreItemOption $record)
		{
		parent::__construct($record);
		}
	}
