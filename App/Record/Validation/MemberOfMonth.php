<?php

namespace App\Record\Validation;

/**
 * Autogenerated. File will not be changed by oneOffScripts\generateValidators.php.  Delete and rerun if you want.
 */
class MemberOfMonth extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, string[]> */
	public static array $validators = [
		'bio' => ['maxlength'],
		'fileNameExt' => ['maxlength'],
		'memberId' => ['required', 'integer'],
		'memberOfMonthId' => ['integer'],
		'month' => ['required', 'maxlength', 'date'],
	];
	}
