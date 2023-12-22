<?php

namespace App\Record\Validation;

class StorePhoto extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'extension' => ['maxlength'],
		'filename' => ['maxlength'],
		'sequence' => ['required', 'integer'],
		'storeItemId' => ['required', 'integer'],
	];

	public function __construct(\App\Record\StorePhoto $record)
		{
		parent::__construct($record);
		}
	}
