<?php

namespace App\Record\Validation;

class SystemEmail extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'email' => ['maxlength'],
		'mailbox' => ['maxlength'],
		'name' => ['maxlength'],
	];

	public function __construct(\App\Record\SystemEmail $record)
		{
		parent::__construct($record);
		}
	}
