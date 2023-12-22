<?php

namespace App\Record\Validation;

class Blog extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'name' => ['required', 'maxlength'],
	];

	public function __construct(\App\Record\Blog $record)
		{
		parent::__construct($record);
		}
	}
