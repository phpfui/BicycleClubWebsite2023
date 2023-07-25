<?php

namespace App\View\Email;

class Settings implements \Stringable
	{
	private array $buttons = [];

	private readonly \App\Table\Setting $settingTable;

	public function __construct(private readonly \App\View\Page $page, private readonly string $settingName, private readonly \App\DB\Interface\EmailData $data)
		{
		$this->settingTable = new \App\Table\Setting();
		}

	public function __toString() : string
		{
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);
		$form->setAreYouSure(false);

		if ($form->isMyCallback())
			{
			$this->settingTable->save($this->settingName . 'Title', $_POST['title']);
			$this->settingTable->saveHtml($this->settingName, $_POST['value']);
			$this->page->setResponse('Saved');
			}
		elseif (\App\Model\Session::checkCSRF() && isset($_POST['test']))
			{
			$this->settingTable->saveHtml($this->settingName, $_POST['value']);
			$this->settingTable->save($this->settingName . 'Title', $_POST['title']);
			$member = \App\Model\Session::getSignedInMember();
			$email = new \App\Model\Email($this->settingName, $this->data);
			$email->setFromMember($member);
			$email->addToMember($member);
			$email->setSubject('Test: ' . $_POST['title']);
			$email->send();
			\App\Model\Session::setFlash('success', 'Test email sent. Check your email.');
			$this->page->redirect();
			}
		else
			{
			$title = new \PHPFUI\Input\Text('title', 'Email Subject (leave blank to disable email)', $this->settingTable->value($this->settingName . 'Title'));
			$form->add($title);
			$fieldSet = new \PHPFUI\FieldSet('Email body');
			$value = $this->settingTable->value($this->settingName);
			$value = \str_replace("\n", '<div></div>', $value);
			$textarea = new \PHPFUI\Input\TextArea('value', '', $value);
			$textarea->htmlEditing($this->page, new \App\Model\TinyMCETextArea());
			$fieldSet->add($textarea);
			$form->add($fieldSet);
			$buttonGroup = new \App\UI\CancelButtonGroup();
			$buttonGroup->addButton($submit);
			$test = new \PHPFUI\Submit('Test Email', 'test');
			$test->addClass('warning');
			$buttonGroup->addButton($test);

			foreach ($this->buttons as $button)
				{
				$buttonGroup->addButton($button);
				}
			$form->add($buttonGroup);

			$fieldSet = new \PHPFUI\FieldSet('Substitution Fields');
			$fieldSet->add(new \App\UI\SubstitutionFields($this->data->toArray()));
			$form->add($fieldSet);
			}

		return (string)$form;
		}

	public function addButton(\PHPFUI\Button $button) : void
		{
		$this->buttons[] = $button;
		}
	}
