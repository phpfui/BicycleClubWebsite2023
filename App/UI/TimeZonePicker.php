<?php

namespace App\UI;

class TimeZonePicker extends \PHPFUI\Input\SelectAutoComplete
	{
	public function __construct(\PHPFUI\Page $page, string $name, string $title, string $value = '')
		{
		parent::__construct($page, $name, $title);
		$this->addOption('Please select a time zone', '', empty($value));
		$this->setArray('timeZones');

		foreach (\DateTimeZone::listIdentifiers(\DateTimeZone::ALL) as $zone)
			{
			$this->addOption($zone, $zone, $value == $zone);
			}
		}
	}
