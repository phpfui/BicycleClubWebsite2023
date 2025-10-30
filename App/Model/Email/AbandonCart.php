<?php

namespace App\Model\Email;

class AbandonCart extends \App\Model\EmailData
	{
	public function __construct(\App\Record\Invoice $invoice = new \App\Record\Invoice(), \App\Record\Member $storeManager = new \App\Record\Member(), string $fullUrl = 'ExamplePaymentURL', string $message = 'You can also cancel your order.')
		{
		if ($invoice->empty())
			{
			$invoice = new \App\Table\Invoice()->getRecordCursor()->current();
			}

		if ($storeManager->empty())
			{
			$storeManager = \App\Model\Session::signedInMemberRecord();
			}
		$this->fields = $invoice->toArray();
		$member = $invoice->member;
		$this->fields['storeManager_firstName'] = $storeManager->firstName;
		$this->fields['storeManager_lastName'] = $storeManager->lastName;
		$this->fields['firstName'] = $member->firstName;
		$this->fields['lastName'] = $member->lastName;
		$this->fields['paymentURL'] = $fullUrl;
		$this->fields['paymentMessage'] = $message;
		}
	}
