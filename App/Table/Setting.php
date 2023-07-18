<?php

namespace App\Table;

class Setting extends \PHPFUI\ORM\Table
{
	protected static string $className = '\\' . \App\Record\Setting::class;

	/** @var array<string,string> */
	private static array $pairs = [];

	public function getStandardPermissionGroup(string $name) : ?\App\Record\Permission
		{
		$permission = new \App\Record\Permission((int)$this->value($this->getGroupName($name)));

		return $permission->loaded() ? $permission : null;
		}

	public function save(string $name, string | int $value) : static
		{
		$record = new \App\Record\Setting($name);

		$record->value = "{$value}";
		$record->name = \substr($name, 0, 30);
		$record->insertOrUpdate();
		self::$pairs[$name] = $value;

		return $this;
		}

	public function saveHtml(string $name, string $html) : static
		{
		return $this->save($name, \App\Tools\TextHelper::cleanUserHtml($html));
		}

	public function saveStandardPermissionGroup(string $name, int $permissionId) : static
		{
		return $this->save($this->getGroupName($name), $permissionId);
		}

	public function value(string $id, string $default = '') : string
		{
		$id = \substr($id, 0, 30);

		if (! isset(self::$pairs[$id]))
			{
			$return = new \App\Record\Setting($id);
			$return = \App\Tools\TextHelper::unhtmlentities($return->value ?? $default);
			self::$pairs[$id] = $return;
			}

		return self::$pairs[$id];
		}

	private function getGroupName(string $name) : string
		{
		return \substr('StandardGroup ' . $name, 0, 30);
		}
	}
