<?php

namespace App\Record\Validation;

class Membership extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'address' => ['maxlength', 'required'],
		'affiliation' => ['maxlength'],
		'allowedMembers' => ['integer'],
		'expires' => ['date'],
		'joined' => ['date'],
		'lastRenewed' => ['date'],
		'pending' => ['integer'],
		'renews' => ['date'],
		'state' => ['maxlength', 'required'],
		'subscriptionId' => ['maxlength'],
		'town' => ['maxlength', 'required'],
		'zip' => ['maxlength', 'required'],
	];

	public function __construct(\App\Record\Membership $record)
		{
		parent::__construct($record);
		}
	}
