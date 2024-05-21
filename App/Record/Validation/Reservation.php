<?php

namespace App\Record\Validation;

class Reservation extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'address' => ['maxlength'],
		'eventId' => ['integer'],
		'invoiceId' => ['integer'],
		'memberId' => ['required', 'integer'],
		'paymentId' => ['integer'],
		'phone' => ['maxlength'],
		'pricePaid' => ['required', 'number'],
		'reservationFirstName' => ['maxlength'],
		'reservationLastName' => ['maxlength'],
		'reservationemail' => ['maxlength', 'email'],
		'signedUpAt' => ['required', 'datetime'],
		'state' => ['maxlength'],
		'town' => ['maxlength'],
		'zip' => ['maxlength'],
	];

	public function __construct(\App\Record\Reservation $record)
		{
		parent::__construct($record);
		}
	}
