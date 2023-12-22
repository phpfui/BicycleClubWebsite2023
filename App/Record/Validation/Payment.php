<?php

namespace App\Record\Validation;

class Payment extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'amount' => ['number'],
		'dateReceived' => ['required', 'date'],
		'enteringMemberNumber' => ['integer'],
		'invoiceId' => ['integer'],
		'membershipId' => ['integer'],
		'paymentDated' => ['date'],
		'paymentNumber' => ['maxlength'],
		'paymentType' => ['integer'],
	];

	public function __construct(\App\Record\Payment $record)
		{
		parent::__construct($record);
		}
	}
