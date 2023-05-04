<?php

namespace App\Model;

class PasswordPolicy
	{
	protected array $fields = [
		'Length' => [null, 'Password must be at least :value characters long'],
		'Upper' => ['/[A-Z]/', 'Password must contain UPPER case characters'],
		'Lower' => ['/[a-z]/', 'Password must contain lower case characters'],
		'Numbers' => ['/[0-9]/', 'Password must contain numbers (0-9)'],
		'Punctuation' => ['/[^A-Za-z0-9]/', 'Password must contain punctuation characters'],
	];

	protected string $prefix = 'PasswordPolicy';

	protected \App\Model\SettingsSaver $settingsSaver;

	public function __construct()
		{
		$this->settingsSaver = new \App\Model\SettingsSaver($this->prefix);
		}

	public function validate(string $password) : array
		{
		$values = $this->settingsSaver->getValues();
		$errors = [];

		foreach ($this->fields as $key => $parameters)
			{
			$value = $values[$this->prefix . $key];

			if (! empty($value))
				{
				if ($parameters[0])
					{
					$matches = [];
					\preg_match($parameters[0], $password, $matches);

					if (! $matches)
						{
						$errors[] = \trans($parameters[1], ['value' => $value]);
						}
					}
				elseif (\strlen($password) < $value)
					{
					$errors[] = \trans($parameters[1], ['value' => $value]);
					}
				}
			}

		return $errors;
		}
	}
