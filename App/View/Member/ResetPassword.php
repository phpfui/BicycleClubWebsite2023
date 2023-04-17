<?php

namespace App\View\Member;

class ResetPassword
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		}

	public function getEmail(string $email) : string
		{
		$form = new \PHPFUI\Form($this->page);
		$fieldSet = new \PHPFUI\FieldSet('Reset Your Password');
		$panel = new \PHPFUI\Panel('We will send you a password reset link if your email address is on file with us.');
		$fieldSet->add($panel);
		$email = new \PHPFUI\Input\Email('email', 'Your email Address', $email);
		$email->setToolTip('Your email address on file with us.  Eg. yourname@gmail.com');
		$email->setRequired();
		$fieldSet->add($email);
		$form->add($fieldSet);
		$buttonGroup = new \PHPFUI\ButtonGroup();
		$email = new \PHPFUI\Submit('Reset My Password via Email', 'resetPassword');
		$buttonGroup->addButton($email);
		$sms = new \App\Model\SMS();

		if ($sms->enabled())
			{
			$text = new \PHPFUI\Submit('Reset My Password via Text', 'resetPassword');
			$text->addClass('warning');
			$buttonGroup->addButton($text);
			}
		$form->add($buttonGroup);

		return "{$form}";
		}
	}
