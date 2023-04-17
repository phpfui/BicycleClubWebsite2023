<?php

namespace App\Cron\Job;

class PurgeCart extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Purges the shopping cart of items left over 5 days.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$invoices = \App\Table\Invoice::getUnpaidBefore(\App\Tools\Date::toString($this->controller->runningAtJD() - 5));
		$invoiceModel = new \App\Model\Invoice();

		foreach ($invoices as $invoice)
			{
			if (! $invoice->paidByCheck && $invoice->unpaidBalance() > 0)
				{
				$invoiceModel->delete($invoice);
				}
			}
		$cartItemTable = new \App\Table\CartItem();
		$cartItemTable->setWhere(new \PHPFUI\ORM\Condition(
			'dateAdded',
			\App\Tools\Date::toString($this->controller->runningAtJD() - 30),
			new \PHPFUI\ORM\Operator\LessThan()
		));
		$cartItemTable->delete();
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(4, 0);
		}
	}
