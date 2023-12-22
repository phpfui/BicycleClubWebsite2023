<?php

namespace App\Record\Validation;

class StartLocation extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'active' => ['integer'],
		'directions' => ['maxlength'],
		'latitude' => ['number'],
		'link' => ['maxlength', 'website'],
		'longitude' => ['number'],
		'name' => ['maxlength', 'required'],
		'address' => ['maxlength'],
		'town' => ['maxlength'],
		'state' => ['maxlength'],
		'zip' => ['maxlength'],
		'nearestExit' => ['maxlength'],
	];

	public function __construct(\App\Record\StartLocation $record)
		{
		parent::__construct($record);
		}
	}
