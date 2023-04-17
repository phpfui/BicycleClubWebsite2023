<?php

class WebBrowserForm
	{
	public array $info = [];

	public array $fields = [];

	public function FindFormFields($name = false, $value = false, $type = false) : array
		{
		$fields = [];

		foreach ($this->fields as $field)
			{
			if ((false === $type || $field['type'] === $type) && (false === $name || $field['name'] === $name) && (false === $value || $field['value'] === $value))
				{
				$fields[] = $field;
				}
			}

		return $fields;
		}

	public function GetFormValue(string $name, $checkval = false, $type = false) : string
		{
		$val = false;

		foreach ($this->fields as $field)
			{
			if ((false === $type || $field['type'] === $type) && $field['name'] === $name)
				{
				if (\is_string($checkval))
					{
					if ($checkval === $field['value'])
						{
						if ('input.radio' == $field['type'] || 'input.checkbox' == $field['type'])
							{
							$val = $field['checked'];
							}
						else
							{
							$val = $field['value'];
							}
						}
					}
				elseif (('input.radio' != $field['type'] && 'input.checkbox' != $field['type']) || $field['checked'])
					{
					$val = $field['value'];
					}
				}
			}

		return $val;
		}

	public function SetFormValue($name, $value, $checked = false, $type = false, $create = false) : bool
		{
		$result = false;

		foreach ($this->fields as $num => $field)
			{
			if ((false === $type || $field['type'] === $type) && $field['name'] === $name)
				{
				if ('input.radio' == $field['type'])
					{
					$this->fields[$num]['checked'] = ($field['value'] === $value ? $checked : false);
					$result = true;
					}
				elseif ('input.checkbox' == $field['type'])
					{
					if ($field['value'] === $value)
						{
						$this->fields[$num]['checked'] = $checked;
						}
					$result = true;
					}
				elseif ('select' != $field['type'] || ! isset($field['options']) || isset($field['options'][$value]))
					{
					$this->fields[$num]['value'] = $value;
					$result = true;
					}
				}
			}
		// Add the field if it doesn't exist.
		if (! $result && $create)
			{
			$this->fields[] = ['id' => false,
				'type' => (false !== $type ? $type : 'input.text'),
				'name' => $name,
				'value' => $value,
				'checked' => $checked, ];
			}

		return $result;
		}

	public function GenerateFormRequest($submitname = false, $submitvalue = false) : array
		{
		$method = $this->info['method'];
		$fields = [];
		$files = [];

		foreach ($this->fields as $field)
			{
			if ('input.file' == $field['type'])
				{
				if (\is_array($field['value']))
					{
					$field['value']['name'] = $field['name'];
					$files[] = $field['value'];
					$method = 'post';
					}
				}
			elseif ('input.reset' == $field['type'] || 'button.reset' == $field['type'])
				{
				}
			elseif ('input.submit' == $field['type'] || 'button.submit' == $field['type'])
				{
				if ((false === $submitname || $field['name'] === $submitname) && (false === $submitvalue || $field['value'] === $submitvalue))
					{
					if (! isset($fields[$field['name']]))
						{
						$fields[$field['name']] = [];
						}
					$fields[$field['name']][] = $field['value'];
					}
				}
			elseif (('input.radio' != $field['type'] && 'input.checkbox' != $field['type']) || $field['checked'])
				{
				if (! isset($fields[$field['name']]))
					{
					$fields[$field['name']] = [];
					}
				$fields[$field['name']][] = $field['value'];
				}
			}

		if ('get' == $method)
			{
			$url = HTTP::ExtractURL($this->info['action']);
			unset($url['query']);
			$url['queryvars'] = $fields;
			$result = ['url' => HTTP::CondenseURL($url),
				'options' => [], ];
			}
		else
			{
			$result = ['url' => $this->info['action'],
				'options' => ['postvars' => $fields,
					'files' => $files, ], ];
			}

		return $result;
		}
	}
