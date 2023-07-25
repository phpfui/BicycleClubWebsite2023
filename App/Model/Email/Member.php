<?php

namespace App\Model\Email;

class Member extends \App\Model\EmailData
	{
	public function __construct(\App\Record\Member $member = new \App\Record\Member())
		{
		if ($member->empty())
			{
			$this->fields = \App\Model\Session::signedInMemberRecord()->toArray();
			$this->fields += \App\Model\Session::signedInMemberRecord()->membership->toArray();
			}
		else
			{
			$this->fields = $member->toArray();
			$this->fields += $member->membership->toArray();
			}
		}
	}
