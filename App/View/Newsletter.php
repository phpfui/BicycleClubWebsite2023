<?php

namespace App\View;

class Newsletter
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		}

	public function Settings() : \PHPFUI\Container
		{
		$container = new \PHPFUI\Container();

		$settingsSaver = new \App\Model\SettingsSaver();

		$saveButton = new \PHPFUI\Submit('Save');
		$form = new \PHPFUI\Form($this->page, $saveButton);
		$fieldSet = new \PHPFUI\FieldSet('Required Settings');
		$fieldSet->add($settingsSaver->generateField('newsletterName', 'Newsletter Name'));
		$fieldSet->add($settingsSaver->generateField('newsletterEmail', 'Newsletter Sending Email Address', 'email'));
		$fieldSet->add($settingsSaver->generateField('newsletterArchiveEmail', 'Newsletter Archive Email Address', 'email'));

		$editor = new \App\UI\MemberPicker($this->page, new \App\Model\MemberPicker('Newsletter Editor'));
		$editControl = $editor->getEditControl();
		$editControl->setRequired();
		$fieldSet->add($editControl);

		if ($form->isMyCallback())
			{
			$settingsSaver->save();
			$this->page->setResponse('Saved');

			return $container;
			}

		$form->add($fieldSet);
		$form->add(new \App\UI\CancelButtonGroup($saveButton));
		$container->add($form);

		return $container;
		}
	}
