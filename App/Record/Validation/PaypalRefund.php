<?php

namespace App\Record\Validation;

/**
 * Autogenerated. File will not be changed by oneOffScripts\generateValidators.php.  Delete and rerun if you want.
 */
class PaypalRefund extends \PHPFUI\ORM\Validator
	{
	/** @var array<string, string[]> */
	public static array $validators = [
		'amount' => ['number'],
		'createdDate' => ['required', 'maxlength', 'date'],
		'createdMemberNumber' => ['integer'],
		'invoiceId' => ['integer'],
		'paypalRefundId' => ['integer'],
		'paypaltx' => ['maxlength'],
		'refundOnDate' => ['maxlength', 'date'],
		'refundedDate' => ['maxlength', 'date'],
		'response' => ['maxlength'],
	];
	}
