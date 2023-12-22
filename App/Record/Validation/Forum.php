<?php

namespace App\Record\Validation;

class Forum extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'attachments' => ['integer'],
		'closed' => ['integer'],
		'description' => ['maxlength', 'required'],
		'email' => ['maxlength', 'required', 'unique'],
		'name' => ['maxlength', 'required', 'unique'],
		'whiteList' => ['maxlength'],
	];

	public function __construct(\App\Record\Forum $record)
		{
		parent::__construct($record);
		}
	}
