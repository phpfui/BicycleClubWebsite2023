<?php

namespace App\DB;

/**
 * @property \App\Record\Invoice $currentRecord
 */
class InvoiceMenu extends \PHPFUI\ORM\VirtualField
	{
	/**
	 * @param array<mixed> $parameters
	 */
	public function getValue(array $parameters) : bool
		{
		$table = new \App\Table\GaEvent();
		$on = new \PHPFUI\ORM\Condition('gaEventId', new \PHPFUI\ORM\Literal('invoiceItem.storeItemId'));
		$table->addJoin('invoiceItem', $on);
		$table->setWhere(new \PHPFUI\ORM\Condition('invoiceItem.invoiceId', $this->currentRecord->invoiceId));

		$showMenu = true;

		foreach ($table->getRecordCursor() as $event)
			{
			if (! $event->allowShopping)
				{
				$showMenu = false;
				}
			}

		return $showMenu;
		}
	}
