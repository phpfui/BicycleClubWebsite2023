<?php

namespace App\View;

class SettingEditor implements \Stringable
	{
	public function __construct(private readonly \App\View\Page $page, private readonly string $settingName, private readonly bool $html = false)
		{
		}

	public function __toString() : string
		{
		$settingTable = new \App\Table\Setting();
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);

		if ($form->isMyCallback())
			{
			$settingTable->saveHtml($this->settingName, $_POST[$this->settingName]);
			$this->page->setResponse('Saved');
			}
		else
			{
			$fieldSet = new \PHPFUI\FieldSet('Message');
			$textarea = new \App\UI\TextAreaImage($this->settingName, '', $settingTable->value($this->settingName));

			if ($this->html)
				{
				$textarea->htmlEditing($this->page, new \App\Model\TinyMCETextArea(new \App\Record\Setting()->getLength('value')));
				}

			$textarea->setRequired();
			$fieldSet->add($textarea);
			$form->add($fieldSet);
			$buttonGroup = new \App\UI\CancelButtonGroup();
			$buttonGroup->addButton($submit);
			$form->add($buttonGroup);
			}

		return (string)$form;
		}
	}
