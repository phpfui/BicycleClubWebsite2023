<?php

namespace App\Record\Validation;

class GaPriceDate extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'date' => ['required', 'date'],
		'gaEventId' => ['required', 'integer'],
		'price' => ['number'],
	];

	public function __construct(\App\Record\GaPriceDate $record)
		{
		parent::__construct($record);
		}
	}
