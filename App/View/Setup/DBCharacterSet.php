<?php

namespace App\View\Setup;

class DBCharacterSet extends \PHPFUI\Container
	{
	public function __construct(\PHPFUI\Page $page, \App\Settings\DB $settings, \App\View\Setup\WizardBar $wizardBar)
		{
		$fields = [
			'charset' => '',
		];
		$save = new \PHPFUI\Submit();
		$wizardBar->addButton($save);
		$form = new \PHPFUI\Form($page, $save);

		if ($form->isMyCallback($save))
			{
			$settings->addFields(\array_intersect_key($_POST, $fields));
			$settings->save();

			$page->setResponse('Saved');

			return;
			}

		$form->add(new \PHPFUI\Header('Set Database Character Set', 4));
		$form->add($wizardBar);
		$fieldSet = new \PHPFUI\FieldSet('Start typing to select a character set (utf8mb4 is recommended)');
		$fieldSet->add(new \App\UI\CharacterSet($page, $settings));
		$form->add($fieldSet);
		$this->add($form);
		}
	}
