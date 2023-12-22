<?php

namespace App\Record\Validation;

class Newsletter extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'date' => ['required', 'date'],
		'dateAdded' => ['required', 'date'],
		'html' => ['maxlength'],
		'size' => ['integer'],
	];

	public function __construct(\App\Record\Newsletter $record)
		{
		parent::__construct($record);
		}
	}
