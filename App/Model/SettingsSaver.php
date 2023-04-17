<?php

namespace App\Model;

class SettingsSaver
	{
	private array $currentValues = [];

	private array $save = [];

	private readonly \App\Table\Setting $settingTable;

	public function __construct(private readonly string $JSONName = '')
		{
		$this->settingTable = new \App\Table\Setting();
		}

	/**
	 * @param \PHPFUI\Input\Tel|\PHPFUI\Input\TextArea|string $type
	 */
	public function generateField(string $name, string $label, string | \PHPFUI\Input $type = 'text', bool $required = true) : \PHPFUI\Input
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

			// @phpstan-ignore-next-line
			if (! ($type instanceof \PHPFUI\Input\Hidden))
				{
				$input->setValue($value);
				}
			}
		else
			{
			$type = \ucwords($type);

			if (! \strcasecmp('CheckBox', $type))
				{
				$required = false;
				$input = new \PHPFUI\Input\CheckBoxBoolean($name, $label, $value);
				}
			else
				{
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

	public function getValue(string $index)
		{
		$this->getValues();

		return $this->currentValues[$index] ?? '';
		}

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

	public function save() : static
		{
		if (empty($this->JSONName))
			{
			\PHPFUI\ORM::beginTransaction();

			foreach ($this->save as $field => $value)
				{
				if (isset($_POST[$field]) && $value != $_POST[$field])
					{
					$this->settingTable->save($field, $_POST[$field]);
					}
				}
			\PHPFUI\ORM::commit();
			}
		else
			{
			$json = [];
			$length = \strlen($this->JSONName);

			foreach ($_POST as $key => $value)
				{
				if ($this->JSONName == \substr($key, 0, $length))
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
