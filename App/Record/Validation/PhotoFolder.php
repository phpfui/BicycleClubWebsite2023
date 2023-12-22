<?php

namespace App\Record\Validation;

class PhotoFolder extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'parentFolderId' => ['integer'],
		'photoFolder' => ['required', 'maxlength'],
	];

	public function __construct(\App\Record\PhotoFolder $record)
		{
		parent::__construct($record);
		}
	}
