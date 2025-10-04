<?php

namespace App\View\Admin;

class HomePage
	{
	public function __construct(private readonly \App\View\Page $page)
		{
		}

	public function __toString() : string
		{
		$submit = new \PHPFUI\Submit();
		$settingsSaver = new \App\Model\SettingsSaver();
		$form = new \PHPFUI\Form($this->page, $submit);
		$callout = new \PHPFUI\Callout('info');
		$callout->add("Enter the numnber of days to show content on the user's home page, or -1 to turn off");
		$form->add($callout);

		$multiColumn = new \PHPFUI\MultiColumn();

		foreach (\App\Enum\HomeNotification::cases() as $case)
			{
			$name = $case->getSettingName();
			$field = $settingsSaver->generateField($name, $case->name(), $settingsSaver->value($name));
			$field->setRequired(false);
			$multiColumn->add($field);

			if (4 === \count($multiColumn))
				{
				$form->add($multiColumn);
				$multiColumn = new \PHPFUI\MultiColumn();
				}
			}

		if (\count($multiColumn))
			{
			$form->add($multiColumn);
			}

		if ($form->isMyCallback())
			{
			$settingsSaver->save($_POST);
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
