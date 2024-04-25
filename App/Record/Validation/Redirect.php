<?php

namespace App\Record\Validation;

class Redirect extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'originalUrl' => ['required', 'maxlength', '!starts_with:/'],
		'redirectUrl' => ['required', 'maxlength', '!starts_with:/'],
	];

	public function __construct(\App\Record\Redirect $record)
		{
		parent::__construct($record);
		}
	}
