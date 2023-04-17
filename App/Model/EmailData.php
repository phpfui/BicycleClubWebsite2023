<?php

namespace App\Model;

class EmailData implements \App\DB\Interface\EmailData
	{
	protected array $fields = [];

	public function toArray() : array
		{
		$settingTable = new \App\Table\Setting();

		foreach (['clubAbbrev', 'clubName', 'calendarName', 'boardName', 'clubLocation', 'domain', 'homePage', ] as $key)
			{
			$this->fields[$key] = $settingTable->value($key);
			}
		\ksort($this->fields);

		return $this->fields;
		}
	}
