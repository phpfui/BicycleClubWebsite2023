<?php

namespace App\Record\Validation;

/**
 * Autogenerated. File will not be changed by oneOffScripts\generateValidators.php.  Delete and rerun if you want.
 */
class StoreItemOption extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, string[]> */
	public static array $validators = [
		'sequence' => ['required', 'integer'],
		'storeItemId' => ['required', 'integer'],
		'storeOptionId' => ['required', 'integer'],
	];
	}
