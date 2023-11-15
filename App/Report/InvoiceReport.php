<?php

namespace App\Report;

class InvoiceReport
	{
	/**
	 * @param array<string,string> $parameters
	 */
	public function download(array $parameters) : void
		{
		$invoiceItem = new \App\Record\InvoiceItem();
		$settings = new \App\Table\Setting();
		$shipped = (int)$parameters['shipped'];
		$title = ' All Invoices';

		if (2 == $shipped)
			{
			$title = ' Unshipped Invoices';
			}
		elseif (1 == $shipped)
			{
			$title = ' Shipped Invoices';
			}
		$csv = ! empty($parameters['csv']);

		$csvWriter = null;
		$pdf = null;

		if ($csv)
			{
			$fileName = "InvoiceReport_{$parameters['startDate']}-{$parameters['endDate']}.csv";
			$csvWriter = new \App\Tools\CSV\FileWriter($fileName);
			$fields = ['First Name', 'Last Name', 'Street Address 1', 'City', 'State', 'Zip Code', ];

			foreach($invoiceItem->getFields() as $key => $value)
				{
				$fields[] = $key;
				}
			$csvWriter->outputRow($fields);
			}
		else
			{
			$pdf = new \PDF_MC_Table();
			$pdf->SetDisplayMode('fullpage');
			$pdf->SetFont('Arial', '', 10);
			$pdf->setNoLines(true);
			$pdf->headerFontSize = 18;
			$pdf->SetAutoPageBreak(true, 2);
			$pdf->SetWidths([20,
				// invoice id
				50,
				// name
				100,
				// address
				15,
				// total
				40,
				// transaction
				20,
				// shipped
			]);
			$pdf->SetHeader(['Invoice Id',
				'Name',
				"Address\nItem",
				"Total\nCount",
				'Transaction',
				'Shipped', ]);
			$pdf->SetAligns(['C',
				'L',
				'L',
				'C',
				'C',
				'C', ]);
			$pdf->AddPage('L', 'Letter');
			$pdf->SetDocumentTitle($settings->value('clubAbbrev') . $title . ' Report Printed On ' . \App\Tools\Date::todayString());
			$pdf->PrintHeader();
			}
		$invoices = \App\Table\Invoice::getPaidByDate($shipped, $parameters['startDate'], $parameters['endDate'], (int)$parameters['points']);
		$customerModel = new \App\Model\Customer();

		foreach ($invoices as $invoice)
			{
			$customer = $customerModel->read((int)$invoice['memberId']);
			$shipped = '';

			if ($invoice['fullfillmentDate'] > '1000-00-00')
				{
				$shipped = $invoice['fullfillmentDate'];
				}
			$itemDetails = \App\Table\InvoiceItem::findItems((int)$invoice['invoiceId'], $parameters['restrict'], $parameters['exclude'], $parameters['text']);

			if (\count($itemDetails))
				{
				if ($csv)
					{
					foreach ($itemDetails as $detail)
						{
						$output = [];
						$output[] = $customer->firstName;
						$output[] = $customer->lastName;
						$output[] = $customer->address;
						$output[] = $customer->town;
						$output[] = $customer->state;
						$output[] = $customer->zip;

						foreach ($output as &$value)
							{
							$value = \App\Tools\TextHelper::unhtmlentities($value);
							}
						unset($value);

						foreach ($detail as $field => $value)
							{
							$output[$field] = $value;
							}
						$csvWriter->outputRow($output);
						}
					}
				else
					{
					$pdf->Row([$invoice['invoiceId'],
						\App\Tools\TextHelper::unhtmlentities($customer->firstName . ' ' . $customer->lastName),
						\App\Tools\TextHelper::unhtmlentities($customer->address . ', ' . $customer->town . ', ' . $customer->state . ', ' . $customer->zip),
						'$' . $invoice['totalPrice'],
						$invoice['paypaltx'],
						$shipped, ]);
					}

				if (! $csv)
					{
					foreach ($itemDetails as $detail)
						{
						$pdf->Row(['',
							'',
							$detail['title'] . ', ' . $detail['detailLine'],
							$detail['quantity'],
							'',
							'', ]);
						}

					if (\strlen((string)$invoice['instructions']))
						{
						$pdf->Row(['',
							'',
							$invoice['instructions'],
							'',
							'',
							'', ]);
						}
					$pdf->Row(['',
						'',
						'',
						'',
						'',
						'', ]);
					}
				}
			}

		if (! $csv)
			{
			$now = \date('Y-m-d');
			$pdf->Output($settings->value('clubAbbrev') . "InvoiceReport-{$now}.pdf", 'I');
			}
		}
	}
