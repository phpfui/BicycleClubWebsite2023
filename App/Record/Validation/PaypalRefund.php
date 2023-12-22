<?php

namespace App\Record\Validation;

class PaypalRefund extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, array<string>> */
	public static array $validators = [
		'amount' => ['number'],
		'createdDate' => ['required', 'date'],
		'createdMemberNumber' => ['integer'],
		'invoiceId' => ['integer'],
		'paypaltx' => ['maxlength'],
		'refundOnDate' => ['date'],
		'refundedDate' => ['date'],
		'response' => ['maxlength'],
	];

	public function __construct(\App\Record\PaypalRefund $record)
		{
		parent::__construct($record);
		}
	}
