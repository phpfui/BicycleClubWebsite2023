<?php

namespace App\Record\Validation;

class MemberCategory extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
	];

	public function __construct(\App\Record\MemberCategory $record)
		{
		parent::__construct($record);
		}
	}
