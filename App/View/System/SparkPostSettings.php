<?php

namespace App\View\System;

class SparkPostSettings
	{
	public function __construct(private readonly \PHPFUI\Page $page)
		{
		}

	public function editSettings() : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$settingsSaver = new \App\Model\SettingsSaver();
		$form = new \PHPFUI\Form($this->page, $submit);
		$fieldSet = new \PHPFUI\FieldSet('SparkPost API Key');
		$fieldSet->add('If you are using <b>SparkPost</b> for sending emails, users can unsubscribe directly through <b>SparkPost</b>. Unfortunately this means you can no longer send them emails through the system.  By supplying this API key and enabling <B>Suppression Lists: Read/Write</b>, the website will unsubscribe them from all emails in their notifications, and remove them from the <b>SparkPost</b> supprssion list so they can still recieve transactional emails like renewals and store purchases.');
		$fieldSet->add($settingsSaver->generateField('SparkPostAPIKey', 'SparkPost API Key (leave blank to turn off)', 'text', false));
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
