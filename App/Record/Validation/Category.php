<?php

namespace App\Record\Validation;

/**
 * Autogenerated. Do not modify. Modify SQL table, then run oneOffScripts\generateValidators.php table_name
 */
class Category extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'category' => ['maxlength'],
		'coordinator' => ['required', 'integer'],
		'description' => ['maxlength'],
		'maxSpeed' => ['maxlength'],
		'memberDefault' => ['required', 'integer'],
		'minSpeed' => ['maxlength'],
		'ordering' => ['required', 'integer'],
	];

	public function __construct(\App\Record\Category $record)
		{
		parent::__construct($record);
		}
	}
