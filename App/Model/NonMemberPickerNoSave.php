<?php

namespace App\Model;

class NonMemberPickerNoSave extends \App\Model\MemberPickerNoSave
	{
	public function __construct(string $name = '')
		{
		parent::__construct($name);
		$this->currentMember = false;
		}
	}
