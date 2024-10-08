<?php

namespace App\Record\Validation;

/**
 * Autogenerated. File will not be changed by oneOffScripts\generateValidators.php.  Delete and rerun if you want.
 */
class JournalItem extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, string[]> */
	public static array $validators = [
		'body' => ['maxlength'],
		'journalItemId' => ['integer'],
		'memberId' => ['integer'],
		'timeSent' => ['required', 'maxlength', 'datetime'],
		'title' => ['maxlength'],
	];
	}
