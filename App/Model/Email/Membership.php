<?php

namespace App\Model\Email;

class Membership extends \App\Model\EmailData
	{
	public function __construct(\App\Record\Membership $membership = new \App\Record\Membership())
		{
		if ($membership->empty())
			{
			$this->fields = \App\Model\Session::getSignedInMember();
			}
		else
			{
			$this->fields = $membership->toArray();
			}
		}
	}
