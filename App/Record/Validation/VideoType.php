<?php

namespace App\Record\Validation;

class VideoType extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'name' => ['required', 'maxlength'],
		'videoTypeId' => ['integer'],
	];

	public function __construct(\App\Record\VideoType $record)
		{
		parent::__construct($record);
		}
	}
