<?php

namespace App\View\Setup;

class TimeZone extends \PHPFUI\Container
	{
	public function __construct(\PHPFUI\Page $page, \App\Settings\DB $settings, \App\View\Setup\WizardBar $wizardBar)
		{
		$field = 'timeZone';
		$save = new \PHPFUI\Submit();
		$wizardBar->addButton($save);
		$form = new \PHPFUI\Form($page, $save);

		if ($form->isMyCallback($save))
			{
			$settings->addFields(\array_intersect_key($_POST, [$field => '', ]));
			$settings->save();

			$page->setResponse('Saved');

			return;
			}

		$form->add(new \PHPFUI\Header('Set Club Time Zone', 4));
		$form->add($wizardBar);
		$fieldSet = new \PHPFUI\FieldSet('Start typing to select the timezone the club uses');
		$fieldSet->add(new \App\UI\TimeZonePicker($page, $field, '', $settings->timeZone ?? 'America/New_York'));
		$form->add($fieldSet);
		$this->add($form);
		}
	}
