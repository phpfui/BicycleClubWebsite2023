<?php

namespace App\Model;

class SettingsSaver
	{
	/**
	 * @var array<string,string>
	 */
	private array $currentValues = [];

	/**
	 * @var array<string,string>
	 */
	private array $save = [];

	private readonly \App\Table\Setting $settingTable;

	public function __construct(private readonly string $JSONName = '')
		{
		$this->settingTable = new \App\Table\Setting();
		}

	public function addInput(\PHPFUI\Input $input) : \PHPFUI\Input
		{
		$this->save[$input->getName()] = $input->getValue();

		return $input;
		}

	/**
	 * @param \PHPFUI\Input\Tel|\PHPFUI\Input\TextArea|string $type
	 */
	public function generateField(string $name, string $label, string | \PHPFUI\Input | \PHPFUI\Input\Hidden $type = 'text', bool $required = true) : \PHPFUI\Input
		{
		if ($this->JSONName)
			{
			$value = $this->getValue($name);
			}
		else
			{
			$value = $this->settingTable->value($name);
			}
		$this->save[$name] = $value;

		if (\is_object($type))
			{
			$input = $type;

			if (! $type instanceof \PHPFUI\Input\Hidden) // @phpstan-ignore-line
				{
				$input->setValue($value);
				}
			}
		else
			{
			$type = \ucwords($type);

			if (\str_contains($type, 'CheckBox'))
				{
				$required = false;
				$input = new \PHPFUI\Input\CheckBoxBoolean($name, $label, (bool)$value);
				}
			else
				{
				foreach (['password', 'private', 'secret', 'key'] as $password)
					{
					if (false != \stripos($name, $password))
						{
						$type = 'PasswordEye';

						break;
						}
					}
				$class = '\\PHPFUI\\Input\\' . $type;

				if (\class_exists($class))
					{
					$input = new $class($name, $label, $value);
					}
				else
					{
					$input = new \PHPFUI\Input\Text($name, $label, $value);
					}
				}
			}

		if ($required)
			{
			$input->setRequired();
			}

		return $input;
		}

	public function getJSONName() : string
		{
		return $this->JSONName;
		}

	public function getValue(string $index) : string
		{
		$this->getValues();

		return $this->currentValues[$index] ?? '';
		}

	/**
	 * @return array<string,string>
	 */
	public function getValues() : array
		{
		if (empty($this->JSONName))
			{
			throw new \Exception(__METHOD__ . ' called on non JSON object');
			}

		if (! $this->currentValues)
			{
			$this->currentValues = \json_decode($this->settingTable->value($this->JSONName), true) ?? [];
			}

		return $this->currentValues;
		}

	/**
	 * @param array<string,string> $post
	 */
	public function save(array $post, bool $preserveValues = false) : static
		{
		if (empty($this->JSONName))
			{
			\PHPFUI\ORM::beginTransaction();

			foreach ($this->save as $field => $value)
				{
				if (isset($post[$field]) && $value != $post[$field])
					{
					$this->settingTable->save($field, $post[$field]);
					}
				}
			\PHPFUI\ORM::commit();
			}
		else
			{
			$json = $preserveValues ? $this->getValues() : [];
			$length = \strlen($this->JSONName);

			foreach ($post as $key => $value)
				{
				if (\substr($key, 0, $length) === $this->JSONName)
					{
					$json[$key] = $value;
					}
				}
			$this->settingTable->save($this->JSONName, \json_encode($json, JSON_THROW_ON_ERROR));
			}

		return $this;
		}

	public function value(string $index) : string
		{
		return $this->settingTable->value($index);
		}
	}
