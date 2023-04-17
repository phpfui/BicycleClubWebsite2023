<?php

namespace App\Model\Email;

class Leader extends \App\Model\EmailData
	{
	public function __construct(\App\Record\Member $member = new \App\Record\Member())
		{
		if ($member->empty())
			{
			$memberTable = new \App\Table\Member();
			$memberTable->addOrderBy('memberId', 'desc');
			$memberTable->setLimit(1);
			$member = $memberTable->getRecordCursor()->current();
			}
		$this->fields = $member->toArray();
		}
	}
