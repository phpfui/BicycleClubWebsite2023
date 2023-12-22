<?php

namespace App\Record\Validation;

class RideComment extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'comment' => ['maxlength'],
		'latitude' => ['number'],
		'longitude' => ['number'],
		'memberId' => ['required', 'integer'],
		'rideId' => ['integer'],
		'time' => ['required', 'datetime'],
	];

	public function __construct(\App\Record\RideComment $record)
		{
		parent::__construct($record);
		}
	}
