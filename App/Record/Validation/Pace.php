<?php

namespace App\Record\Validation;

class Pace extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'categoryId' => ['integer'],
		'maxRiders' => ['required', 'integer'],
		'maxSpeed' => ['maxlength'],
		'minSpeed' => ['maxlength'],
		'ordering' => ['required', 'integer'],
		'pace' => ['maxlength'],
	];

	public function __construct(\App\Record\Pace $record)
		{
		parent::__construct($record);
		}
	}
