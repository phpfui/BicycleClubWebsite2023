<?php

namespace App\Model\Email;

class GaRider extends \App\Model\EmailData
	{
	public function __construct(\App\Record\GaRider $rider = new \App\Record\GaRider(), string $message = 'General Admission message')
		{
		if ($rider->empty())
			{
			$gaRiderTable = new \App\Table\GaRider();
			$gaRiderTable->addOrderBy('gaRiderId', 'desc');
			$gaRiderTable->setLimit(1);
			$rider = $gaRiderTable->getRecordCursor()->current();
			}
		$this->fields = $rider->toArray();
		$this->fields['message'] = $message;
		}
	}
