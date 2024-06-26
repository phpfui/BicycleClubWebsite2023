<?php

namespace App\Record\Validation;

class Member extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'acceptedWaiver' => ['datetime'],
		'allowTexting' => ['integer'],
		'cellPhone' => ['maxlength'],
		'deceased' => ['integer'],
		'discountCount' => ['integer'],
		'email' => ['required', 'maxlength', 'email', 'unique'],
		'emailAnnouncements' => ['integer'],
		'emailNewsletter' => ['integer'],
		'emergencyContact' => ['maxlength'],
		'emergencyPhone' => ['maxlength'],
		'extension' => ['maxlength'],
		'firstName' => ['required', 'maxlength'],
		'geoLocate' => ['integer'],
		'journal' => ['integer'],
		'lastLogin' => ['datetime'],
		'lastName' => ['required', 'maxlength'],
		'volunteerPoints' => ['integer'],
		'license' => ['maxlength'],
		'loginAttempts' => ['maxlength'],
		'membershipId' => ['integer'],
		'newRideEmail' => ['integer'],
		'password' => ['maxlength'],
		'pendingLeader' => ['integer'],
		'phone' => ['maxlength'],
		'profileHeight' => ['integer'],
		'profileWidth' => ['integer'],
		'profileX' => ['integer'],
		'profileY' => ['integer'],
		'rideComments' => ['integer'],
		'rideJournal' => ['integer'],
		'showNoPhone' => ['integer'],
		'showNoStreet' => ['integer'],
		'showNoTown' => ['integer'],
		'showNothing' => ['integer'],
		'verifiedEmail' => ['integer'],
	];

	public function __construct(\App\Record\Member $record)
		{
		parent::__construct($record);
		}
	}
