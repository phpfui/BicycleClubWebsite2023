<?php

namespace App\Record\Validation;

class Photo extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'extension' => ['required', 'maxlength'],
		'memberId' => ['integer'],
		'photo' => ['required', 'maxlength'],
		'photoFolderId' => ['required', 'integer'],
		'public' => ['required', 'integer'],
		'taken' => ['datetime'],
		'thumbnail' => ['required', 'integer'],
		'uploaded' => ['required', 'datetime'],
	];

	public function __construct(\App\Record\Photo $record)
		{
		parent::__construct($record);
		}
	}
