<?php

namespace App\Cron\Job;

class UnshippedInvoices extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Send the Unshipped Invoices report email.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$itemResult = \App\Table\InvoiceItem::getUnshippedItems();

		if (\count($itemResult))
			{
			$today = \App\Tools\Date::todayString(-3);
			$bcc = false;

			foreach ($itemResult as $invoice)
				{
				if ($invoice['orderDate'] < $today)
					{
					$bcc = true;
					}
				}
			$pullList = new \App\Report\PullList();
			$pdf = $pullList->generate($itemResult);
			$settings = new \App\Table\Setting();
			$abbrev = $settings->value('clubAbbrev');
			$email = new \App\Tools\EMail();
			$email->setBody("See attached pull report of unshipped {$abbrev} invoices.");
			$email->setSubject($abbrev . ' Unshipped Invoices');

			if ($bcc)
				{
				$addresses = ['president', 'vicepresident'];

				foreach ($addresses as $address)
					{
					$system = new \App\Record\SystemEmail(['mailbox' => $address]);

					if ($system->loaded())
						{
						$email->addBCC($system->email, $system->name);
						}
					}
				}
			$memberPicker = new \App\Model\MemberPicker('Store Manager');
			$email->setFromMember($memberPicker->getMember());
			$email->addToMember($memberPicker->getMember());
			$email->addAttachment($pdf->Output('', 'S'), $pullList->getFileName());
			$email->send();
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(20, 0);
		}
	}
