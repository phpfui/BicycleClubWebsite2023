<?php

namespace App\View\System;

class SMTPSettings
	{
	public function __construct(private readonly \PHPFUI\Page $page)
		{
		}

	public function edit() : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$buttonGroup = new \App\UI\CancelButtonGroup($submit);

		$member = \App\Model\Session::signedInMemberRecord();

		if ($member->email)
			{
			$testButton = new \PHPFUI\Submit('Test', 'action');
			$testButton->addClass('warning');
			$buttonGroup->addButton($testButton);
			}
		$defaults = new \PHPFUI\Submit('IONOS Defaults', 'action');
		$defaults->addClass('secondary');
		$defaults->setConfirm('Are you sure you want to reset to the IONOS defaults?');
		$buttonGroup->addButton($defaults);

		$settingsSaver = new \App\Model\SettingsSaver('SMTP');
		$form = new \PHPFUI\Form($this->page, $submit);
		$fieldSet = new \PHPFUI\FieldSet('SMTP Server Settings');
		$link = new \PHPFUI\Link('https://app.sparkpost.com', 'SparkPost');
		$fieldSet->add("You need to set up an SMTP server to send emails. Leave the Host field blank to use the local server's email settings, or use a separately hosted SMTP server.
										We reccommend using {$link}.
										They have a free low volume account and it is easy to upgrade to more volume.");
		$host = $settingsSaver->generateField('SMTPHost', 'Host (leave blank for local email server)');
		$host->setRequired(false);
		$fieldSet->add($host);
		$fieldSet->add($settingsSaver->generateField('SMTPUsername', 'Username'));
		$fieldSet->add($settingsSaver->generateField('SMTPPassword', 'Password', 'PasswordEye'));
		$fieldSet->add($settingsSaver->generateField('SMTPSecure', 'SMTPSecure (tls or ssl)'));
		$fieldSet->add($settingsSaver->generateField('SMTPPort', 'Port', 'Number'));
		$fieldSet->add($settingsSaver->generateField('SMTPLog', 'Log Emails to Slack', 'CheckBox'));
		$fieldSet->add($settingsSaver->generateField('SMTPLimit', 'Limit emails to send at one time (0 is unlimited)', 'Number'));
		$form->add($fieldSet);

		if ($form->isMyCallback())
			{
			$settingsSaver->save($_POST);
			$this->page->setResponse('Saved');
			}
		elseif (\App\Model\Session::checkCSRF() && isset($_POST['action']))
			{
			switch ($_POST['action'])
				{
				case 'Test':
					$email = new \App\Tools\EMail(false);
					$email->addToMember($member->toArray());
					$email->setFromMember($member->toArray());
					$email->setSubject('SMTP Setup test email from ' . \emailServerName());
					$email->setBody('Your SMTP setup is correct!');
					$error = $email->send();

					if ($error)
						{
						\App\Model\Session::setFlash('alert', $error);
						}
					else
						{
						\App\Model\Session::setFlash('success', 'Email sent. Check your inbox or spam folder.');
						}

					break;

				case 'IONOS Defaults':
					$settings = [];
					$settings['SMTPHost'] = 'smtp.ionos.com';
					$settings['SMTPUsername'] = '*@' . \emailServerName();
					$settings['SMTPSecure'] = 'STARTTLS';
					$settings['SMTPPort'] = '587';
					$settings['SMTPLimit'] = '5';
					$settingsSaver->save($settings, true);
					\App\Model\Session::setFlash('success', 'Reset to IONOS defaults');

					break;

				}
			$this->page->redirect();
			}
		else
			{
			$form->add($buttonGroup);
			}

		return $form;
		}
	}
