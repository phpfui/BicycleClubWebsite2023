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
		$settingSaver = new \App\Model\SettingsSaver(static::$prefix);
		$form->add($settingSaver->generateField(static::$prefix . 'Length', 'Minimum Password Length', 'Number', false));
		$form->add($settingSaver->generateField(static::$prefix . 'Upper', 'Require Upper Case Characters', 'CheckBox', false));
		$form->add($settingSaver->generateField(static::$prefix . 'Lower', 'Require Lower Case Characters', 'CheckBox', false));
		$form->add($settingSaver->generateField(static::$prefix . 'Numbers', 'Require Numbers (0-9)', 'CheckBox', false));
		$form->add($settingSaver->generateField(static::$prefix . 'Punctuation', 'Require Punctuation', 'CheckBox', false));
		$form->add($submit);

		if ($form->isMyCallback($submit))
			{
			$settingSaver->save($_POST);
			$this->page->setResponse('Saved');
			}

		return $form;
		}

	public function getErrorMessage() : string
		{
		if (! static::$values)
			{
			return '';
			}

		$messages = [];
		$value = (int)static::$values[static::$prefix . 'Length'];

		if ($value)
			{
			$messages[] = "be at least {$value} characters long";
			}

		if (static::$values[static::$prefix . 'Upper'])
			{
			$messages[] = 'have UPPER case';
			}

		if (static::$values[static::$prefix . 'Lower'])
			{
			$messages[] = 'have lower case';
			}

		if (static::$values[static::$prefix . 'Numbers'])
			{
			$messages[] = 'have numbers';
			}

		if (static::$values[static::$prefix . 'Punctuation'])
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

		if (! static::$values)
			{
			return null;
			}

		$js = [];
		$value = (int)static::$values[static::$prefix . 'Length'];

		if ($value)
			{
			$js[] = "(to.length>={$value})";
			}

		if (static::$values[static::$prefix . 'Upper'])
			{
			$js[] = '((/[A-Z]/).test(to))';
			}

		if (static::$values[static::$prefix . 'Lower'])
			{
			$js[] = '((/[a-z]/).test(to))';
			}

		if (static::$values[static::$prefix . 'Numbers'])
			{
			$js[] = '((/[0-9]/).test(to))';
			}

		if (static::$values[static::$prefix . 'Punctuation'])
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

		if (! static::$values)
			{
			return null;
			}

		foreach (static::$fields as $name => $parameters)
			{
			$value = (int)static::$values[static::$prefix . $name];

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
