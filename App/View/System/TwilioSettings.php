<?php

namespace App\View\System;

class TwilioSettings
	{
	public function __construct(private readonly \PHPFUI\Page $page)
		{
		}

	public function edit() : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$settingsSaver = new \App\Model\SettingsSaver('Twilio');
		$form = new \PHPFUI\Form($this->page, $submit);
		$fieldSet = new \PHPFUI\FieldSet('Twilio Settings');
		$fieldSet->add('You need to set up a paid <b>Twilio SMS</b> account to send SMS messages. Leave the <b>Account SID</b> field blank to disable SMS.
										You can set up a <a href="https://www.twilio.com/" target="_blank">Twilio account here</a>.');
		$sid = $settingsSaver->generateField('TwilioSID', 'Account SID (leave blank for no SMS support)');
		$sid->setRequired(false);
		$fieldSet->add($sid);
		$token = $settingsSaver->generateField('TwilioToken', 'Auth Token');
		$token->setRequired(false);
		$fieldSet->add($token);
		$tel = new \PHPFUI\Input\Tel($this->page, 'TwilioNumber', 'SMS Phone Number');
		$number = $settingsSaver->generateField('TwilioNumber', 'SMS Phone Number', $tel);
		$number->setRequired(false);
		$fieldSet->add($number);
		$defaultArea = $settingsSaver->generateField('TwilioDefaultAreaCode', 'Default Area Code');
		$defaultArea->setRequired(false);
		$fieldSet->add($defaultArea);

		$form->add($fieldSet);

		if ($form->isMyCallback())
			{
			$settingsSaver->save($_POST);
			$this->page->setResponse('Saved');
			}
		else
			{
			$form->add(new \App\UI\CancelButtonGroup($submit));
			}

		return $form;
		}
	}
