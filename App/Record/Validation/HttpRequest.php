<?php

namespace App\Record\Validation;

class HttpRequest extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'REQUEST_METHOD' => ['maxlength'],
		'REQUEST_URI' => ['maxlength'],
		'_get' => ['maxlength'],
		'_post' => ['maxlength'],
	];

	public function __construct(\App\Record\HttpRequest $record)
		{
		parent::__construct($record);
		}
	}
