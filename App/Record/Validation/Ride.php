<?php

namespace App\Record\Validation;

class Ride extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'RWGPSId' => ['integer'],
		'accident' => ['integer'],
		'averagePace' => ['number'],
		'cueSheetId' => ['integer'],
		'dateAdded' => ['required', 'datetime'],
		'description' => ['maxlength'],
		'elevation' => ['integer'],
		'maxRiders' => ['required', 'integer'],
		'memberId' => ['required', 'integer'],
		'mileage' => ['maxlength'],
		'numberOfRiders' => ['integer'],
		'paceId' => ['required', 'integer'],
		'pointsAwarded' => ['required', 'integer'],
		'regrouping' => ['maxlength'],
		'releasePrinted' => ['datetime'],
		'rideDate' => ['required', 'date'],
		'rideStatus' => ['integer'],
		'signupNotifications' => ['integer'],
		'startLocationId' => ['integer'],
		'startTime' => [],
		'targetPace' => ['number', 'minvalue:5', 'maxvalue:25'],
		'title' => ['maxlength'],
		'unaffiliated' => ['integer'],
		'restStop' => ['maxlength'],
	];

	public function __construct(\App\Record\Ride $record)
		{
		parent::__construct($record);
		}
	}
