<?php

namespace App\UI;

class RegroupPolicy
	{
	private string $name = 'regroupingPolicy';

	private \App\Model\SettingsSaver $settingsSaver;

	public function __construct(private \PHPFUI\Page $page, private ?string $policy = null)
		{
		$this->settingsSaver = new \App\Model\SettingsSaver();
		}

	public function edit() : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);
		$form->add('List regrouping choices, one per line, in the order you want them shown. If this is left blank, 50 unique regrouping policies from the most recent rides will be used.');
		$fieldSet = new \PHPFUI\FieldSet('Regrouping Choices');
		$text = new \PHPFUI\Input\TextArea($this->name, '', $this->settingsSaver->value($this->name));
		$fieldSet->add($this->settingsSaver->generateField($this->name, '', $text, false));
		$form->add($fieldSet);

		if ($form->isMyCallback())
			{
			$this->settingsSaver->save($_POST);
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

	public function getControl() : \PHPFUI\Input\Select
		{
		$options = \explode("\n", $this->settingsSaver->value($this->name));

		if (\count($options) > 1)
			{
			$control = new \PHPFUI\Input\Select('regrouping', 'Regrouping Policy');
			$control->setToolTip('Select your regrouping policy from the list.');

			foreach ($options as $value)
				{
				$control->addOption($value, $value, $value == $this->policy);
				}
			}
		else
			{
			$control = new \PHPFUI\Input\SelectAutoComplete($this->page, 'regrouping', 'Regrouping Policy', true);
			$control->setToolTip('Select an existing policy or enter your own.');
			$control->addOption($this->policy ?? '', $this->policy ?? '', true);

			$rideTable = new \App\Table\Ride();
			$rideTable->addSelect('regrouping')->setDistinct()->setLimit(50)->addOrderBy('rideId', 'desc');

			foreach ($rideTable->getArrayCursor() as $option)
				{
				$value = $option['regrouping'];
				$control->addOption($value, $value, $value == $this->policy);
				}
			}

		$control->setRequired();

		return $control;
		}
	}
