<?php

namespace App\Record\Validation;

class Permission extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'menu' => ['maxlength'],
		'system' => ['required', 'integer'],
		'name' => ['required', 'maxlength', 'unique'],
	];

	public function __construct(\App\Record\Permission $record)
		{
		parent::__construct($record);
		}
	}
