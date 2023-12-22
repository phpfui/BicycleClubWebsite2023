<?php

namespace App\Record\Validation;

class BikeShopArea extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'area' => ['maxlength'],
		'state' => ['maxlength'],
	];

	public function __construct(\App\Record\BikeShopArea $record)
		{
		parent::__construct($record);
		}
	}
