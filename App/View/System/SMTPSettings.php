<?php

namespace App\View\System;

class SMTPSettings
	{
	public function __construct(private readonly \PHPFUI\Page $page)
		{
		}

	public function editSettings() : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
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
		$fieldSet->add($settingsSaver->generateField('SMTPPort', 'Port', 'number'));
		$fieldSet->add($settingsSaver->generateField('SMTPLog', 'Log Emails to Slack', 'CheckBox'));
		$form->add($fieldSet);

		if ($form->isMyCallback())
			{
			$settingsSaver->save();
			$this->page->setResponse('Saved');
			}
		else
			{
			$form->add(new \App\UI\CancelButtonGroup($submit));
			}

		return $form;
		}
	}
