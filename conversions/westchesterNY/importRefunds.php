<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

$gaRiderTable = new \App\CRUD\GaRider();
$csvReader = new \App\Tools\CSV\FileReader('../gadonations.csv');

$noRefunds = [];

foreach ($csvReader as $donation)
	{
  $email = $donation['Email'];
  $start = \strpos($email, '<');
  $end = \strpos($email, '>');

  if (false !== $start && false != $end)
		{
	$email = \substr($email, $start + 1, $end - $start - 1);
		}
  $key = ['eventId' => 43, 'email' => $email];
  $rider = $gaRiderTable->read($key);
  $noRefunds[$rider['riderId']] = $donation['Amount'];
	}

$sql = 'select i.invoiceId,i.paypaltx,paypalpaid,price,storeItemDetailId from invoice i left join invoiceItem ii on i.invoiceId=ii.invoiceId where storeItemId=43 and ii.title like "%golden apple%" and i.paypaltx>""';
$result = \PHPFUI\ORM::getArrayCursor($sql);

echo '<pre>';
\print_r($result);
echo '</pre>';

$refunds = $nukeRefunds = $invoices = [];

echo 'firstName,lastName,email,amount,paypaltx<br>';

foreach ($result as $transaction)
	{
	$paypaltx = $transaction['paypaltx'];
  $invoices[$paypaltx] = $transaction['invoiceId'];

	if (! isset($refunds[$paypaltx]))
		{
		$refunds[$paypaltx] = 0;
		}
	$refunds[$paypaltx] += $transaction['price'];

  if (isset($noRefunds[$transaction['storeItemDetailId']]))
		{
	$nukeRefunds[] = $paypaltx;
		}
  else
		{
		$rider = $gaRiderTable->read($transaction['storeItemDetailId']);
	echo "{$rider['firstName']},{$rider['lastName']},{$rider['email']},{$transaction['price']},{$transaction['paypaltx']}<br>";
		}
	}

foreach ($nukeRefunds as $paypaltx)
	{
  unset($refunds[$paypaltx]);
	}

$payPalRefundTable = new \App\CRUD\PayPalRefund();

foreach ($refunds as $paypaltx => $amount)
	{
	$refund = [];
	$refund['invoiceId'] = $invoices[$paypaltx];
	$refund['amount'] = $amount;
	$refund['paypaltx'] = $paypaltx;
  $refund['createdDate'] = \Tools\Date::today();
  $refund['refundOnDate'] = \Tools\Date::today() + 1;
	$refund['createdMemberNumber'] = 2590;
	$payPalRefundTable->insert($refund);
	}
