<?php

namespace App\Record\Validation;

class BikeShop extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'address' => ['maxlength', 'required'],
		'bikeShopAreaId' => ['integer'],
		'contact' => ['maxlength'],
		'email' => ['maxlength', 'email'],
		'name' => ['maxlength', 'required'],
		'notes' => ['maxlength'],
		'phone' => ['maxlength', 'required'],
		'state' => ['maxlength', 'required'],
		'town' => ['maxlength', 'required'],
		'url' => ['maxlength', 'website'],
		'zip' => ['maxlength', 'required'],
	];

	public function __construct(\App\Record\BikeShop $record)
		{
		parent::__construct($record);
		}
	}
