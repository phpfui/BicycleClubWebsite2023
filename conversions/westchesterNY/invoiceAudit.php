<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

function makeDate(string $date) : string
	{
	[$month, $day, $year] = \explode('/', $date);

	return \sprintf('%04d', (int)$year) . '-' . \sprintf('%2d', (int)$month) . '-' . \sprintf('%02d', (int)$day);
	}

$csvReader = new \App\Tools\CSV\FileReader('Paypal Oct1_20 2022.CSV');

/**
 * Date
 * Time
 * Name
 * Gross
 * From Email Address
 * Transaction ID
 * Shipping Address
 * Item Title
 * Invoice Number
 * Quantity
 * Address Line 1
 * Address Line 2/District/Neighborhood
 * Town/City
 * State/Province/Region/County/Territory/Prefecture/Republic
 * Zip/Postal Code
 * Contact Phone Number
 */
$paymentTable = new \App\Table\Payment();

foreach ($csvReader as $row)
	{
	if ((float)$row['Gross'] < 0)
		{
		continue;
		}
	$txn = $row['Transaction ID'];

	$payment = new \App\Record\Payment();
	$payment->read(['paymentNumber' => $txn]);

	if ($payment->paymentNumber != $txn)
		{
		$invoiceId = (int)$row['Invoice Number'];
		$invoice = new \App\Record\Invoice($invoiceId);
		$invoiceModel = new \App\Model\Invoice();

		if (! $invoice->loaded())
			{
			echo "Invoice {$invoiceId} not found for {$row['Name']}\n";

			continue;
			}
		$payment_amount = (float)$row['Gross'];
		echo "update Invoice {$invoiceId} for {$row['Name']} {$row['Item Title']}\n";
		$invoiceModel->executePayment($invoice, $txn, $payment_amount);
		}
	}

exit();

$invoice = new \App\Record\Invoice(12290);

if ($invoice->loaded())
	{
	$invoice->delete();
	}
$invoice = new \App\Record\Invoice(12297);

if ($invoice->loaded())
	{
	$invoice->delete();
	}

$startDate = '2022-10-05';

$storeOrderTable = new \App\Table\StoreOrder();
$condition = new \PHPFUI\ORM\Condition('added', $startDate, new \PHPFUI\ORM\Operator\GreaterThan());
$condition->and(new \PHPFUI\ORM\Condition('optionsSelected', '', new \PHPFUI\ORM\Operator\GreaterThan()));
$storeOrderTable->setWhere($condition);

$memberOrders = [];

foreach ($storeOrderTable->getRecordCursor() as $storeOrder)
	{
	if (! isset($memberOrders[$storeOrder->memberId]))
		{
		$memberOrders[$storeOrder->memberId] = [];
		}
	$memberOrders[$storeOrder->memberId][] = clone $storeOrder;
	}

$invoiceTable = new \App\Table\Invoice();
$condition = new \PHPFUI\ORM\Condition('orderDate', $startDate, new \PHPFUI\ORM\Operator\GreaterThanEqual());
$invoiceTable->setWhere($condition);

$paypal = 0;
$unpaid = 0;
$points = 0;

foreach ($invoiceTable->getRecordCursor() as $invoice)
	{
	if (isset($memberOrders[$invoice->memberId]))
		{
		if (empty($invoice->paypaltx))
			{
			if ($invoice->pointsUsed >= $invoice->totalPrice)
				{
				++$points;
				}
			elseif (\fmod($invoice->totalPrice, 30.0))
				{
				$member = $invoice->member;
				echo $member->fullName() . "\n";
				\print_r($invoice);
				++$unpaid;
				}
			}
		else
			{
			echo $invoice->member->fullName() . "\n";
			++$paypal;
			}
		$memberOrders[$invoice->memberId] = \confirmInvoice($invoice, $memberOrders[$invoice->memberId]);
		}


//		echo "{$invoice->invoiceId} price {$invoice->totalPrice} {$invoice->paypaltx} {$invoice->paymentDate}\n";

	}

echo "paypal {$paypal} unpaid {$unpaid} points {$points}\n";

foreach ($memberOrders as $memberId => $storeOrders)
	{
	if (\count($storeOrders))
		{
		$member = new \App\Record\Member($memberId);
		echo $member->fullName() . "\n";
		\print_r($storeOrders);
		}
	}

function confirmInvoice(\App\Record\Invoice $invoice, array $memberOrders) : array
	{
	foreach ($invoice->InvoiceItemChildren as $invoiceItem)
		{
		foreach ($memberOrders as $index => $storeOrder)
			{
			if ($storeOrder->storeItemId == $invoiceItem->storeItemId && $storeOrder->optionsSelected == $invoiceItem->detailLine && $storeOrder->quantity == $invoiceItem->quantity)
				{
//				echo "Removing from " . $invoice->member->fullName() . " {$storeOrder->storeItemId} {$storeOrder->optionsSelected}\n";
				unset($memberOrders[$index]);
				}
			}
		}

	return $memberOrders;
	}
