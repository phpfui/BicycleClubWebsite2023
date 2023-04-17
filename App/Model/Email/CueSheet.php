<?php

namespace App\Model\Email;

class CueSheet extends \App\Model\EmailData
	{
	public function __construct(\App\Record\CueSheet $cuesheet = new \App\Record\CueSheet(), string $message = 'Rejection reason text')
		{
		if ($cuesheet->empty())
			{
			$cuesheetTable = new \App\Table\CueSheet();
			$cuesheetTable->addOrderBy('cuesheetId', 'desc');
			$cuesheetTable->setLimit(1);
			$cuesheet = $cuesheetTable->getRecordCursor()->current();
			}
		$this->fields = $cuesheet->toArray();
		$member = $cuesheet->member;
		$startLocation = $cuesheet->startLocation;
		$this->fields['message'] = $message;
		$this->fields['startLocation'] = $startLocation->name;
		$this->fields['firstName'] = $member->firstName;
		$this->fields['lastName'] = $member->lastName;
		$this->fields['terrain'] = $cuesheet->terrain();
		$this->fields['downloadLink'] = $cuesheet->getFullNameLink();
		}
	}
