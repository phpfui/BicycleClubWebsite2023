<?php

namespace App\Record\Validation;

/**
 * Autogenerated. Do not modify. Modify SQL table, then run oneOffScripts\generateValidators.php table_name
 */
class Blog extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'name' => ['required', 'maxlength'],
	];

	public function __construct(\App\Record\Blog $record)
		{
		parent::__construct($record);
		}
	}
