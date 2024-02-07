<?php

namespace App\Record\Validation;

class RideSignup extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'attended' => ['integer'],
		'comments' => ['maxlength'],
		'rideComments' => ['integer'],
		'signedUpTime' => ['required', 'datetime'],
		'status' => ['integer'],
	];

	public function __construct(\App\Record\RideSignup $record)
		{
		parent::__construct($record);
		}
	}
