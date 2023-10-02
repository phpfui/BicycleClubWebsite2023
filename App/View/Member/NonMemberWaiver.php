<?php

namespace App\View\Member;

class NonMemberWaiver
	{
	private array $fields = [];

	public function __construct(private readonly \App\View\Page $page)
		{
		$this->fields[] = new \PHPFUI\Input\Email('email', 'Your email address');
		$this->fields[] = new \PHPFUI\Input\Text('firstName', 'First Name');
		$this->fields[] = new \PHPFUI\Input\Text('lastName', 'Last Name');
		$this->fields[] = new \App\UI\TelUSA($page, 'cellPhone', 'Cell Phone');
		$this->fields[] = new \PHPFUI\Input\Text('emergencyContact', 'Emergency Contact Name');
		$this->fields[] = new \App\UI\TelUSA($page, 'emergencyPhone', 'Emergency Contact Phone');
		}

	public function addField(\PHPFUI\Input $input) : static
		{
		$this->fields[] = $input;

		return $this;
		}

	public function sign(string $text) : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit('I Agree');
		$submit->setDisabled();
		$form = new \PHPFUI\Form($this->page);
		$settings = new \App\Table\Setting();

		$fieldSet = new \PHPFUI\FieldSet('Please acknowledge by checking the box below');
		$fieldSet->add($text);
		$form->add($fieldSet);
		$cb = new \PHPFUI\Input\CheckBox('agreedToWaiver', 'I agree to the above terms', 1);
		$cb->setChecked(false);
		$elementId = $submit->getId();
		$dollar = '$';
		$cb->setAttribute('onclick', "{$dollar}(\"#{$elementId}\").toggleClass(\"disabled\")");
		$form->add($cb);

		foreach ($this->fields as $field)
			{
			$field->setRequired();
			$form->add($field);
			}

		$form->add($submit);

		return $form;
		}
	}
