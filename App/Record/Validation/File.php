<?php

namespace App\Record\Validation;

class File extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'extension' => ['required', 'maxlength'],
		'file' => ['required', 'maxlength'],
		'folderId' => ['required', 'integer'],
		'fileName' => ['required', 'maxlength'],
		'memberId' => ['integer'],
		'public' => ['required', 'integer'],
		'uploaded' => ['datetime'],
	];

	public function __construct(\App\Record\File $record)
		{
		parent::__construct($record);
		}
	}
