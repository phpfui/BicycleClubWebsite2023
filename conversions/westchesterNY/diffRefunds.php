<?php

// set the server name which determains which db to use
$_SERVER['SERVER_NAME'] = $argv[1] ?? 'localhost';

include __DIR__ . '/../../common.php';

echo "Loaded settings file {$dbSettings->getLoadedFileName()}\n";

echo '<pre>';

$csvReader = new \App\Tools\CSVReader('../final12202017.csv');
$refunds = [];
$count = -1;

foreach ($csvReader as $refund)
	{
  if (isset($refunds[$refund['paypaltx']]))
		{
	echo "found dupe {$refund['paypaltx']}\n";
	++$count;
		}
  $refunds[$refund['paypaltx']] = $refund;
	}
echo "{$count} dups\n";

return;
$invoiceTable = new \App\CRUD\Invoice();
$payPalRefundTable = new \App\CRUD\PayPalRefund();
$saved = $payPalRefundTable->readMultiple();

foreach ($saved as $refund)
	{
  if (isset($refunds[$refund['paypaltx']]))
		{
	unset($refunds[$refund['paypaltx']]);
		}
	}

foreach ($refunds as $refund)
	{
	$invoice = $invoiceTable->read(['paypaltx' => $refund['paypaltx']]);

  if ($invoice && 17 == \strlen($invoice['palpaytx']))
		{
	$data = [
		'amount' => 50.00,
		'invoiceId' => $invoice['invoiceId'],
		'paypaltx' => $refund['paypaltx'],
		'refundOnDate' => \Tools\Date::today(),
		'createdDate' => \Tools\Date::today(),
		'refundedDate' => 0,
		'response' => '',
		'createdMemberNumber' => 2590,
	];
		$payPalRefundTable->insert($data);
		}
	}

/*
[id] => 96
[firstName] => Colin
[lastName] => Millar
[email] => pfmillar@optonline.net
[amount] => 50
[paypaltx] => 5RA352917K6089400

[id] => 125
[firstName] => Daniel
[lastName] => Goldberger
[email] => dmgcd@optonline.net
[amount] => 50
[paypaltx] => 2S0363812C8978146

[id] => 132
[firstName] => David
[lastName] => Ettenberg
[email] => david@campshane.com
[amount] => 50
[paypaltx] => 6RL78975VH5859821

[id] => 289
[firstName] => Doug
[lastName] => Zarookian
[email] => dougzarookian@aol.com
[amount] => 50
[paypaltx] => 7XG806115B967053H

[id] => 304
[firstName] => Martin
[lastName] => Oka
[email] => joannearon@nyc.rr.com
[amount] => 50
[paypaltx] => 03A52294SE644205M

[id] => 171
[firstName] => Stephen
[lastName] => McCulloch Jr
[email] => smcculloch@holulihanparnes.com
[amount] => 50
[paypaltx] => 6FD76415055209638
 */
