<?php

namespace App\Record\Validation;

class Folder extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'parentFolderId' => ['integer'],
		'name' => ['required', 'maxlength'],
	];

	public function __construct(\App\Record\Folder $record)
		{
		parent::__construct($record);
		}
	}
