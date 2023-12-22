<?php

namespace App\Record\Validation;

class AuditTrail extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'additional' => ['maxlength'],
		'input' => ['maxlength'],
		'memberId' => ['required', 'integer'],
		'statement' => ['maxlength'],
		'time' => ['required', 'datetime'],
	];

	public function __construct(\App\Record\AuditTrail $record)
		{
		parent::__construct($record);
		}
	}
