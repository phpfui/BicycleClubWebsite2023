<?php

namespace App\Record\Validation;

/**
 * Autogenerated. File will not be changed by oneOffScripts\generateValidators.php.  Delete and rerun if you want.
 */
class Permission extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, string[]> */
	public static array $validators = [
		'menu' => ['maxlength'],
		'permissionId' => ['integer'],
		'system' => ['required', 'integer'],
		'name' => ['required', 'maxlength', 'unique'],
	];
	}
