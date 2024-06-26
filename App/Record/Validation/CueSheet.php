<?php

namespace App\Record\Validation;

class CueSheet extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'RWGPSId' => ['integer'],
		'dateAdded' => ['required', 'date'],
		'description' => ['maxlength'],
		'destination' => ['maxlength'],
		'elevation' => ['integer'],
		'memberId' => ['integer'],
		'mileage' => ['number'],
		'name' => ['maxlength'],
		'pending' => ['required', 'integer'],
		'pointsAwarded' => ['required', 'integer'],
		'revisionDate' => ['date'],
		'startLocationId' => ['integer'],
		'terrainId' => ['integer'],
	];

	public function __construct(\App\Record\CueSheet $record)
		{
		parent::__construct($record);
		}
	}
