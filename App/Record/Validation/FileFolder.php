<?php

namespace App\Record\Validation;

class FileFolder extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'fileFolder' => ['required', 'maxlength'],
		'parentFolderId' => ['integer'],
	];

	public function __construct(\App\Record\FileFolder $record)
		{
		parent::__construct($record);
		}
	}
