<?php

namespace App\Record\Validation;

class PhotoTag extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'frontToBack' => ['required', 'integer'],
		'leftToRight' => ['required', 'integer'],
		'memberId' => ['integer'],
		'photoId' => ['required', 'integer'],
		'photoTag' => ['required', 'maxlength'],
		'taggerId' => ['required', 'integer'],
	];

	public function __construct(\App\Record\PhotoTag $record)
		{
		parent::__construct($record);
		}
	}
