<?php

namespace App\Record\Validation;

class Video extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'description' => ['required', 'maxlength'],
		'editor' => ['required', 'integer'],
		'fileName' => ['required', 'maxlength'],
		'hits' => ['required', 'integer'],
		'lastEdited' => ['required', 'integer'],
		'public' => ['integer'],
		'title' => ['required', 'maxlength'],
		'videoDate' => ['required', 'date'],
		'videoId' => ['integer'],
		'videoTypeId' => ['integer'],
	];

	public function __construct(\App\Record\Video $record)
		{
		parent::__construct($record);
		}
	}
