<?php

namespace App\Table;

class Setting extends \PHPFUI\ORM\Table
{
	protected static string $className = '\\' . \App\Record\Setting::class;

	private static array $pairs = [];

	public function save(string $name, string | int $value) : static
		{
		$record = new \App\Record\Setting($name);

		$record->value = "{$value}";

		if ($record->loaded())
			{
			$record->update();
			}
		else
			{
			$record->name = $name;
			$record->insert();
			}
		self::$pairs[$name] = $value;

		return $this;
		}

	public function value(string $id, string $default = '') : string
		{
		if (! isset(self::$pairs[$id]))
			{
			$return = new \App\Record\Setting($id);
			$return = \App\Tools\TextHelper::unhtmlentities($return->value ?? $default);
			self::$pairs[$id] = $return;
			}

		return self::$pairs[$id];
		}
	}
