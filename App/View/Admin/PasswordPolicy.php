<?php

namespace App\View\Admin;

class PasswordPolicy extends \App\Model\PasswordPolicy
	{
	public function __construct(private \App\View\Page $page)
		{
		parent::__construct();
		}

	public function edit() : \PHPFUI\Form
		{
		$submit = new \PHPFUI\Submit();
		$form = new \PHPFUI\Form($this->page, $submit);
		$form->add($this->settingsSaver->generateField($this->prefix . 'Length', 'Minimum Password Length', 'Number', false));
		$form->add($this->settingsSaver->generateField($this->prefix . 'Upper', 'Require Upper Case Characters', 'CheckBox', false));
		$form->add($this->settingsSaver->generateField($this->prefix . 'Lower', 'Require Lower Case Characters', 'CheckBox', false));
		$form->add($this->settingsSaver->generateField($this->prefix . 'Numbers', 'Require Numbers (0-9)', 'CheckBox', false));
		$form->add($this->settingsSaver->generateField($this->prefix . 'Punctuation', 'Require Punctuation', 'CheckBox', false));
		$form->add($submit);

		if ($form->isMyCallback($submit))
			{
			$this->settingsSaver->save();
			$this->page->setResponse('Saved');
			}

		return $form;
		}

	public function getErrorMessage() : string
		{
		$values = $this->settingsSaver->getValues();
		if (! $values)
			{
			return '';
			}

		$messages = [];
		$value = (int)$values[$this->prefix . 'Length'];

		if ($value)
			{
			$messages[] = "be at least {$value} characters long";
			}

		if ($values[$this->prefix . 'Upper'])
			{
			$messages[] = 'have UPPER case';
			}

		if ($values[$this->prefix . 'Lower'])
			{
			$messages[] = 'have lower case';
			}

		if ($values[$this->prefix . 'Numbers'])
			{
			$messages[] = 'have numbers';
			}

		if ($values[$this->prefix . 'Punctuation'])
			{
			$messages[] = 'have punctuation';
			}

		if (! $messages)
			{
			return '';
			}

		return 'Must ' . \implode(', ', $messages);
		}

	public function getPasswordValidator() : ?\PHPFUI\Validator
		{
		$validator = new \PHPFUI\Validator('password');

		$values = $this->settingsSaver->getValues();
		if (! $values)
			{
			return null;
			}

		$js = [];
		$value = (int)$values[$this->prefix . 'Length'];

		if ($value)
			{
			$js[] = "(to.length>={$value})";
			}

		if ($values[$this->prefix . 'Upper'])
			{
			$js[] = '((/[A-Z]/).test(to))';
			}

		if ($values[$this->prefix . 'Lower'])
			{
			$js[] = '((/[a-z]/).test(to))';
			}

		if ($values[$this->prefix . 'Numbers'])
			{
			$js[] = '((/[0-9]/).test(to))';
			}

		if ($values[$this->prefix . 'Punctuation'])
			{
			$js[] = '((/[^A-Za-z0-9]/).test(to))';
			}

		if (! $js)
			{
			return null;
			}

		$validator->setJavaScript($validator->getJavaScriptTemplate(\implode('&&', $js)));

		return $validator;
		}

	public function getValidatedPassword(string $name, string $label, ?string $value = '') : \PHPFUI\Input\PasswordEye
		{
		$password = new \PHPFUI\Input\PasswordEye($name, $label, $value);

		$validator = $this->getPasswordValidator();

		if ($validator)
			{
			$errorMessage = $this->getErrorMessage();
			$password->setToolTip($errorMessage);
			$this->page->addAbideValidator($validator);
			$password->setValidator($validator, $errorMessage, $password->getId());
			}
		else
			{
			$password->setToolTip('Your new password should be 8 characters long, have letters, numbers and punctuation');
			}

		return $password;
		}

	public function list() : ?\PHPFUI\UnorderedList
		{
		$ul = new \PHPFUI\UnorderedList();
		$values = $this->settingsSaver->getValues();
		if (! $values)
			{
			return null;
			}

		foreach ($this->fields as $name => $parameters)
			{
			$value = (int)$values[$this->prefix . $name];

			if (! empty($value))
				{
				$ul->addItem(new \PHPFUI\ListItem(\trans($parameters[1], ['value' => $value])));
				}
			}

		if (\count($ul))
			{
			return $ul;
			}

		return null;
		}
	}
