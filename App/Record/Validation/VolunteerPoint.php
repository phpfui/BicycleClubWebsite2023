<?php

namespace App\Record\Validation;

/**
 * Autogenerated. Do not modify. Modify SQL table, then run oneOffScripts\generateValidators.php table_name
 */
class VolunteerPoint extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'pointsAwarded' => ['required', 'integer'],
	];

	public function __construct(\App\Record\VolunteerPoint $record)
		{
		parent::__construct($record);
		}
	}
