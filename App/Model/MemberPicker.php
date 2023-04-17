<?php

namespace App\Model;

class MemberPicker extends \App\Model\MemberPickerBase
	{
	private readonly \App\Table\Setting $settingTable;

	public function __construct(string $name = '')
		{
		$this->settingTable = new \App\Table\Setting();
		parent::__construct($name);
		}

	public function getMember(string $title = '', bool $returnSomeone = true) : array
		{
		$member = $this->getSavedMember($title, $returnSomeone);

		if (empty($member['email']) && $returnSomeone)
			{
			$member['firstName'] = 'Web';
			$member['memberId'] = 0;
			$member['lastName'] = 'Master';
			$member['address'] = $this->settingTable->value('memberAddr');
			$member['town'] = $this->settingTable->value('memberTown');
			$member['state'] = '';
			$member['zip'] = '';
			$server = \strtolower((string)$_SERVER['SERVER_NAME']);

			if ('www.' == \substr($server, 0, 4))
				{
				$server = \substr($server, 4);
				}
			$member['email'] = 'webmaster@' . $server;
			}

		return $member;
		}

	public function getSavedMember(string $title = '', bool $returnSomeone = true) : array
		{
		if (empty($title))
			{
			$title = $this->name;
			}

		if (empty($title))
			{
			throw new \Exception('Name is not set in ' . __METHOD__);
			}

		$memberId = $this->settingTable->value($title);

		if (! (int)$memberId)
			{
			$memberId = $this->settingTable->value('Web Master');
			}

		$member = new \App\Record\Member($memberId);

		return $member->toArray();
		}

	public function save(int $value) : void
		{
		if (empty($this->name))
			{
			throw new \Exception('Name is not set in ' . __METHOD__);
			}
		$this->settingTable->save($this->name, $value);
		}
	}
