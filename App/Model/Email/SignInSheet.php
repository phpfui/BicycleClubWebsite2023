<?php

namespace App\Model\Email;

class SignInSheet extends \App\Model\EmailData
	{
	public function __construct(\App\Record\SigninSheet $signinsheet = new \App\Record\SigninSheet(), string $message = 'SignIn Sheet test message')
		{
		if ($signinsheet->empty())
			{
			$signinsheetTable = new \App\Table\SigninSheet();
			$signinsheetTable->addOrderBy('signinSheetId', 'desc');
			$signinsheetTable->setLimit(1);
			$signinsheet = $signinsheetTable->getRecordCursor()->current();
			}
		$this->fields = $signinsheet->toArray();
		$this->fields['message'] = $message;
		\ksort($this->fields);
		}
	}
