<?php

namespace App\View\Setup;

class TestEmail extends \PHPFUI\Container
	{
	public function __construct(\PHPFUI\Page $page, \App\View\Setup\WizardBar $wizardBar)
		{
		$this->add(new \PHPFUI\Header('Test Sending Emails', 4));

		$submit = new \PHPFUI\Submit('Test Email');
		$form = new \PHPFUI\Form($page);

		$submit->addClass('success');
		$wizardBar->addButton($submit);

		$form->add($wizardBar);

		if ($form->isMyCallback($submit))
			{
			$email = new \App\Tools\EMail(true);
			$email->setBody('This is a test email body');
			$email->setSubject('This is a test email');
			$email->setFrom($_POST['email']);
			$email->addTo($_POST['email']);
			$email->useSMTPServer(! $_POST['localServer']);
			$error = $email->send();

			\PHPFUI\Session::setFlash('post', $_POST);

			if ($error)
				{
				\PHPFUI\Session::setFlash('alert', "<b>Error sending email: </b>{$error}");
				}
			else
				{
				\PHPFUI\Session::setFlash('success', 'Email sent. Please check your inbox (or spam folder) to make sure you received it');
				}

			$page->redirect();

			return;
			}
		$post = \PHPFUI\Session::getFlash('post');

		$fieldSet = new \PHPFUI\FieldSet('Please enter an email address of where to send the test email');
		$email = new \PHPFUI\Input\Email('email', 'Email address to receive test email', $post['email'] ?? '');
		$email->setRequired();
		$fieldSet->add($email);
		$useLocalServer = new \PHPFUI\Input\CheckBoxBoolean('localServer', 'Use the built in account default local server', $post['localServer'] ?? false);
		$fieldSet->add($useLocalServer);
		$form->add($fieldSet);

		$this->add($form);
		}
	}
