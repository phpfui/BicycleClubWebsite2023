<?php

namespace App\Model\Email;

class GaRider extends \App\Model\EmailData
	{
	/**
	 * @param array<int> $gaEventIds
	 */
	public function __construct(array $gaEventIds, \App\Record\GaRider $rider = new \App\Record\GaRider())
		{
		if ($rider->empty())
			{
			$gaRiderTable = new \App\Table\GaRider();

			if (\count($gaEventIds))
				{
				$ids = [];

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
		$options = $rider->optionsSelected;

		foreach ($options as $option)
			{
			$this->fields[$option->optionName] = $option->selectionName;
			}
		}
	}
