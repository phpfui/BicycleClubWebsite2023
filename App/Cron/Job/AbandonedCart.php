<?php

namespace App\Cron\Job;

class AbandonedCart extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Send abandon cart emails.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$invoiceTable = new \App\Table\Invoice();
		$unpaidDates = [\App\Tools\Date::toString($this->controller->runningAtJD() - 1)];
		$unpaidDates[] = \App\Tools\Date::toString($this->controller->runningAtJD() - 4);
		$invoices = $invoiceTable->getUnpaidOn($unpaidDates);
		$invoiceModel = new \App\Model\Invoice();
		$settingTable = new \App\Table\Setting();
		$clubAbbrev = $settingTable->value('clubAbbrev');
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
				$email = new \App\Tools\EMail();
				$memberPicker = new \App\Model\MemberPicker('Store Manager');
				$storeManager = $memberPicker->getMember();
				$email->setFromMember($storeManager);
				$email->setToMember($member->toArray());

				$email->setSubject("Did you forget to pay for your recent {$clubAbbrev} order?");
				$pdf = $invoiceModel->generatePDF($invoice);
				$chairs = $invoiceModel->getChairs($invoice);

				foreach ($chairs as $address => $name)
					{
					$email->addBCCMember(['email' => $address, 'firstName' => $name, 'lastName' => '']);
					}
				$html = "Dear {$member->firstName},<p>We noticed you did not pay for your recent order (see attached). Don't worry, we can help you pay with PayPal." .
					'<p>PayPal accepts major credit cards and does not require you to join or give them anything other than the normal credit card info you ' .
					"do when you buy anything on the web. PayPal takes security extremely seriously and has never had any issues, plus we don't see your credit card info.<p>" .
					"<a href='{$fullUrl}'>Click on this link to pay with PayPal</a>. {$additional}<p>Of course, if you have any questions, please feel " .
					'free to reply to this email and we will be happy to answer any questions.<p>And thanks for your order!<p>' .
					$storeManager['firstName'] . ' ' . $storeManager['lastName'];
				$email->addAttachment($pdf->Output('S'), $invoiceModel->getFileName($invoice));
				$email->setBody($html);
				$email->setHtml();
				$email->send();
				}
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(10, 0);
		}
	}
