<?php

namespace App\Record\Validation;

/**
 * Autogenerated. File will not be changed by oneOffScripts\generateValidators.php.  Delete and rerun if you want.
 */
class GaSelection extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, string[]> */
	public static array $validators = [
		'additionalPrice' => ['number'],
		'gaEventId' => ['required', 'integer'],
		'gaOptionId' => ['required', 'integer'],
		'gaSelectionId' => ['required', 'integer'],
		'ordering' => ['required', 'integer'],
		'selectionName' => ['required', 'maxlength'],
		'csvValue' => ['maxlength'],
	];
	}
