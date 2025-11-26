<?php

namespace App\Cron\Job;

class AbandonedCart extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Send abandoned cart emails.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$settingTable = new \App\Table\Setting();
		$dayIntervals = $settingTable->value('abandonCartDaysBack');
		$unpaidDates = [];

		foreach (\explode(',', $dayIntervals) as $daysBack)
			{
			$daysBack = (int)$daysBack;

			if ($daysBack > 0)
				{
				$unpaidDates[] = \App\Tools\Date::toString($this->controller->runningAtJD() - (int)$daysBack);
				}
			}

		if (! \count($unpaidDates))
			{
			return;
			}

		$clubAbbrev = $settingTable->value('clubAbbrev');
		$invoiceMode = new \App\Model\Invoice();

		foreach ($invoiceMode->findUnpaidDuplicateInvoices($unpaidDates) as $invoice)
			{
			if ($invoice->unpaidBalance()) // double check it is unpaid
				{
				$invoice->delete();
				}
			}

		$invoiceTable = new \App\Table\Invoice();
		$invoices = $invoiceTable->getUnpaidOn($unpaidDates);
		$invoiceModel = new \App\Model\Invoice();
		$url = $this->controller->getSchemeHost();

		foreach ($invoices as $invoice)
			{
			$memberId = $invoice->memberId;

			if ($memberId > 0)
				{
				$member = new \App\Record\Member($memberId);
				$fullUrl = $url . '/Store/Invoice/myUnpaid';
				$additional = 'You can also cancel your order.';
				}
			else
				{
				$member = new \App\Record\Customer(0 - $memberId);
				$fullUrl = $url . '/Store/pay/' . $invoice->invoiceId . '/Store';
				$additional = '';
				}

			if (\filter_var($member->email ?? '', FILTER_VALIDATE_EMAIL))
				{
				$memberPicker = new \App\Model\MemberPicker('Store Manager');
				$storeManagerArray = $memberPicker->getMember();
				$storeManager = new \App\Record\Member();
				$storeManager->setFrom($storeManagerArray);
				$email = new \App\Model\Email('abandonCart', new \App\Model\Email\AbandonCart($invoice, $storeManager, $fullUrl, $additional));

				if (! $email->getSubject())
					{
					continue;
					}

				$email->setToMember($member->toArray());
				$email->setFromMember($storeManagerArray);
				$pdf = $invoiceModel->generatePDF($invoice);
				$email->addAttachment($pdf->Output('S'), $invoiceModel->getFileName($invoice));

				$chairs = $invoiceModel->getChairs($invoice);

				foreach ($chairs as $address => $name)
					{
					$email->addBCCMember(['email' => $address, 'firstName' => $name, 'lastName' => '']);
					}

				$email->send();
				}
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(10, 0);
		}
	}
