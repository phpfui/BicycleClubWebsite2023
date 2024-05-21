<?php

namespace App\Model\Email;

class GaRider extends \App\Model\EmailData
	{
	/**
	 * @param array<int> $gaEventIds
	 */
	public function __construct(array $gaEventIds, \App\Record\GaRider $rider = new \App\Record\GaRider())
		{
		$ids = [];

		if ($rider->empty())
			{
			$gaRiderTable = new \App\Table\GaRider();

			if (\count($gaEventIds))
				{
				foreach ($gaEventIds as $eventId => $value)
					{
					if ($value)
						{
						$ids[] = $eventId;
						}
					}
				$gaRiderTable->setWhere(new \PHPFUI\ORM\Condition('gaEventId', $ids, new \PHPFUI\ORM\Operator\In()));
				}
			$gaRiderTable->addOrderBy('gaRiderId');
			$gaRiderTable->setLimit(1);
			$rider = $gaRiderTable->getRecordCursor()->current();
			}

		$this->fields = $rider->toArray();

		// set blank values by default
		if (\count($ids))
			{
			$gaOptionTable = new \App\Table\GaOption();
			$gaOptionTable->setWhere(new \PHPFUI\ORM\Condition('gaEventId', $ids, new \PHPFUI\ORM\Operator\In()));

			foreach ($gaOptionTable->getRecordCursor() as $gaOption)
				{
				$this->fields[$gaOption->optionName] = '';
				}
			}

		$options = $rider->optionsSelected;

		foreach ($options as $option)
			{
			$this->fields[$option->optionName] = $option->selectionName;
			}
		}
	}
