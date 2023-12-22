<?php

namespace App\Record\Validation;

class Event extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'checks' => ['integer'],
		'directionsUrl' => ['maxlength', 'website'],
		'door' => ['integer'],
		'endTime' => ['maxlength', 'gt_field:startTime'],
		'eventDate' => ['required', 'date'],
		'lastRegistrationDate' => ['required', 'date', 'lte_field:eventDate'],
		'location' => ['maxlength'],
		'maxDiscounts' => ['required', 'integer'],
		'maxReservations' => ['integer'],
		'membersOnly' => ['integer'],
		'newMemberDate' => ['date'],
		'newMemberDiscount' => ['required', 'number', 'lte_field:price'],
		'numberReservations' => ['integer'],
		'organizer' => ['integer'],
		'paypal' => ['integer'],
		'price' => ['required', 'number'],
		'publicDate' => ['date', 'lte_field:registrationStartDate', 'lte_field:eventDate'],
		'registrationStartDate' => ['date', 'lte_field:lastRegistrationDate', 'lte_field:eventDate'],
		'startTime' => ['maxlength', 'lt_field:endTime'],
		'title' => ['maxlength'],
	];

	public function __construct(\App\Record\Event $record)
		{
		parent::__construct($record);
		}
	}
