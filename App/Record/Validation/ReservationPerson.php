<?php

namespace App\Record\Validation;

class ReservationPerson extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'email' => ['maxlength', 'email'],
		'eventId' => ['integer'],
		'firstName' => ['maxlength'],
		'lastName' => ['maxlength'],
		'reservationId' => ['integer'],
	];

	public function __construct(\App\Record\ReservationPerson $record)
		{
		parent::__construct($record);
		}
	}
