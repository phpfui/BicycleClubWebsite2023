<?php

namespace App\Cron\Job;

class PayPalRefunds extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Process PayPal Refunds.';
		}

	public function run() : void
		{
		$paypalRefundTable = new \App\Table\PaypalRefund();

		$refunds = $paypalRefundTable->getPendingRefunds();
		$paypalModel = new \App\Model\PayPal('Refunds');
//		$apiContext = $paypalModel->getRestAPIContext();
		$apiContext = null;
		$logger = new \App\Tools\Logger(__CLASS__);
		$count = 0;

		foreach ($refunds as $refund)
			{
			if ($refund->refundedDate > '0000-00-00')
				{
				continue;
				}
			$amt = new \PHPFUI\PayPal\Amount();
			$amt->setTotal($refund->amount)->setCurrency('USD');

			$paypalRefund = new \PHPFUI\PayPal\Refund();
			$paypalRefund->setAmount($amt);
			$sale = new \PHPFUI\PayPal\Sale();
			$sale->setId($refund->paypaltx);

			try
				{
				$refundedSale = $sale->refund($paypalRefund, $apiContext);
				$refund->response = $refundedSale;
				$refund->refundedDate = \App\Tools\Date::todayString();
				$refund->update();
				++$count;
				}
			catch (\Exception $exception)
				{
				$logger->debug('PayPal Exception');
				$logger->debug($exception->getMessage());
				}
			catch (\Exception $exception)
				{
				$logger->debug('PHP Exception');
				$logger->debug($exception->getMessage());
				}

			if (++$count >= 10)
				{
				break;
				}
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runHourly();
		}
	}
