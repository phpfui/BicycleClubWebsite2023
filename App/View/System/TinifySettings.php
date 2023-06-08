<?php

namespace App\View\System;

class TinifySettings
	{
	public function __construct(private readonly \PHPFUI\Page $page)
		{
		}

	public function edit() : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$settingsSaver = new \App\Model\SettingsSaver();
		$form = new \PHPFUI\Form($this->page, $submit);
		$fieldSet = new \PHPFUI\FieldSet('Tinify Image Reduction API Key');
		$fieldSet->add('This website compresses all uploaded photos with <b>tinypng.com API</b> for faster loading images. You will need to get a free key from them at <a href="https://tinypng.com/developers" target=_blank>https://tinypng.com/developers</a>');
		$fieldSet->add($settingsSaver->generateField('TinifyKey', 'Tinypng Key (leave blank to turn off)', 'text', false));
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
