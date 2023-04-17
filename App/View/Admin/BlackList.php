<?php

namespace App\View\Admin;

class BlackList
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		}

	public function emails() : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$settingsSaver = new \App\Model\SettingsSaver();
		$form = new \PHPFUI\Form($this->page, $submit);
		$form->add('List emails or domains (@xyz.com) to blacklist, one per line.');
		$fieldSet = new \PHPFUI\FieldSet('Email addresses to blacklist');
		$name = 'BlackListedEmails';
		$text = new \PHPFUI\Input\TextArea($name, '', $settingsSaver->value($name));
		$fieldSet->add($settingsSaver->generateField($name, '', $text));
		$form->add($fieldSet);

		if ($form->isMyCallback())
			{
			$settingsSaver->save();
			$this->page->setResponse('Saved');
			}
		else
			{
			$buttonGroup = new \App\UI\CancelButtonGroup();
			$buttonGroup->addButton($submit);
			$form->add($buttonGroup);
			}

		return $form;
		}
	}
