<?php

namespace App\Record\Validation;

class CueSheetVersion extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'cueSheetId' => ['integer'],
		'dateAdded' => ['required', 'date'],
		'extension' => ['maxlength'],
		'link' => ['maxlength', 'website'],
		'memberId' => ['integer'],
	];

	public function __construct(\App\Record\CueSheetVersion $record)
		{
		parent::__construct($record);
		}
	}
