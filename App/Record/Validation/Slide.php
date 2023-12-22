<?php

namespace App\Record\Validation;

class Slide extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'added' => ['required', 'datetime'],
		'caption' => ['maxlength'],
		'extension' => ['maxlength'],
		'memberId' => ['integer', 'minvalue:0'],
		'photoId' => ['integer'],
		'sequence' => ['required', 'integer'],
		'showCaption' => ['required', 'integer'],
		'slideShowId' => ['required', 'integer'],
		'updated' => ['datetime'],
		'url' => ['maxlength', 'website|istarts_with:/'],
	];

	public function __construct(\App\Record\Slide $record)
		{
		parent::__construct($record);
		}
	}
