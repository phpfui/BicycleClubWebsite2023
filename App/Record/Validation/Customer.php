<?php

namespace App\Record\Validation;

class Customer extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'address' => ['maxlength', 'required'],
		'email' => ['maxlength', 'required', 'email'],
		'firstName' => ['maxlength', 'required'],
		'lastName' => ['maxlength', 'required'],
		'password' => ['maxlength'],
		'state' => ['maxlength', 'required'],
		'town' => ['maxlength', 'required'],
		'zip' => ['maxlength', 'required'],
	];

	public function __construct(\App\Record\Customer $record)
		{
		parent::__construct($record);
		}
	}
