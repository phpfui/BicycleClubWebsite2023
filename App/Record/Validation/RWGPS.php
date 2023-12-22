<?php

namespace App\Record\Validation;

class RWGPS extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'club' => ['required', 'integer'],
		'csv' => ['maxlength'],
		'description' => ['required', 'maxlength'],
		'elevation' => ['integer'],
		'feetPerMile' => ['required', 'number'],
		'lastUpdated' => ['date'],
		'latitude' => ['number'],
		'longitude' => ['number'],
		'mileage' => ['number'],
		'query' => ['maxlength'],
		'startLocationId' => ['integer'],
		'state' => ['required', 'maxlength'],
		'status' => ['integer'],
		'title' => ['required', 'maxlength'],
		'town' => ['required', 'maxlength'],
		'zip' => ['required', 'maxlength'],
	];

	public function __construct(\App\Record\RWGPS $record)
		{
		parent::__construct($record);
		}
	}
