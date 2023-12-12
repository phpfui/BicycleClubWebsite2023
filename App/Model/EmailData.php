<?php

namespace App\Model;

class EmailData implements \App\DB\Interface\EmailData
	{
	/** @var array<string, string> */
	protected array $fields = [];

	public function toArray() : array
		{
		$settingTable = new \App\Table\Setting();

		foreach (['clubAbbrev', 'clubName', 'calendarName', 'boardName', 'clubLocation', 'domain', 'homePage', ] as $key)
			{
			$this->fields[$key] = $settingTable->value($key);
			}

		$boardMemberTable = new \App\Table\BoardMember();

		foreach ($boardMemberTable->getRecordCursor() as $boardMember)
			{
			$member = $boardMember->member;
			$this->fields[$boardMember->title] = $member->fullName();
			$this->fields[$boardMember->title . 'Email'] = $member->email;
			$this->fields[$boardMember->title . 'Cell'] = $member->cellPhone;
			}

		\ksort($this->fields);

		return $this->fields;
		}
	}
