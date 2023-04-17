<?php

namespace App\Model\Email;

class Calendar extends \App\Model\EmailData
	{
	public function __construct(\App\Record\Calendar $calendar = new \App\Record\Calendar(), string $message = 'Calendar event message')
		{
		if ($calendar->empty())
			{
			$calendarTable = new \App\Table\Calendar();
			$calendarTable->addOrderBy('calendarId', 'desc');
			$calendarTable->setLimit(1);
			$calendar = $calendarTable->getRecordCursor()->current();
			}
		$this->fields = $calendar->toArray();
		unset($this->fields['password']);
		$sha = \sha1($calendar->privateEmail . $calendar->privateContact);
		$settingTable = new \App\Table\Setting();
		$this->fields['message'] = $message;
		$this->fields['editLink'] = new \PHPFUI\Link($settingTable->value('homePage') . "/Calendar/edit/{$calendar->calendarId}/{$sha}", 'Edit your submission');
		}
	}
