<?php

namespace App\Record\Validation;

class PermissionGroup extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'revoked' => ['required', 'integer'],
	];

	public function __construct(\App\Record\PermissionGroup $record)
		{
		parent::__construct($record);
		}
	}
