<?php

namespace App\Model;

class MemberPickerNoSave extends \App\Model\MemberPickerBase
	{
	public function __construct(string $name = '')
		{
		parent::__construct($name);
		}

	public function getMember(string $title = '', bool $returnSomeone = true) : array
		{
		if (\is_array($this->member) && ! empty($this->member))
			{
			return $this->member;
			}
		$member = [];
		$member['memberId'] = 0;
		$member['firstName'] = '';
		$member['lastName'] = '';
		$member['address'] = '';
		$member['town'] = '';
		$member['state'] = '';
		$member['zip'] = '';
		$member['email'] = '';

		return $member;
		}

	public function save(int $value) : void
		{
		}
	}
