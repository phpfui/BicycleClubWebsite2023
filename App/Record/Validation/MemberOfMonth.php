<?php

namespace App\Record\Validation;

class MemberOfMonth extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'bio' => ['maxlength'],
		'fileNameExt' => ['maxlength'],
		'memberId' => ['required', 'integer'],
		'month' => ['required', 'date'],
	];

	public function __construct(\App\Record\MemberOfMonth $record)
		{
		parent::__construct($record);
		}
	}
