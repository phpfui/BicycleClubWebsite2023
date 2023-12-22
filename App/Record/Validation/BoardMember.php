<?php

namespace App\Record\Validation;

class BoardMember extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'description' => ['maxlength'],
		'extension' => ['maxlength'],
		'rank' => ['integer'],
		'title' => ['maxlength', 'required'],
	];

	public function __construct(\App\Record\BoardMember $record)
		{
		parent::__construct($record);
		}
	}
