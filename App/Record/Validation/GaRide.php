<?php

namespace App\Record\Validation;

/**
 * Autogenerated. Do not modify. Modify SQL table, then run oneOffScripts\generateValidators.php table_name
 */
class GaRide extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'description' => ['maxlength'],
		'distance' => ['integer'],
		'endTime' => ['time'],
		'extraPrice' => ['number'],
		'gaEventId' => ['required', 'integer'],
		'startTime' => ['time'],
	];

	public function __construct(\App\Record\GaRide $record)
		{
		parent::__construct($record);
		}
	}
