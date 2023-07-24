<?php

namespace App\Report;

class Finance
	{
	/**
	 * @param array<string,string> $request
	 */
	public function downloadChecksReceived(array $request) : void
		{
		$payments = \App\Table\Payment::getByDate($start = $request['start'], $end = $request['end'], \App\View\Finance::getPaymentRequest($request), (bool)$request['myChecks']);
		$paymentTypes = \App\Table\Payment::getPaymentTypes();

		if (\count($payments))
			{
			$settings = new \App\Table\Setting();
			$memberTable = new \App\Table\Member();
			$pdf = new \PDF_MC_Table();
			$pdf->SetDisplayMode('fullpage');
			$pdf->SetFont('Arial', '', 10);
			$pdf->setNoLines(true);
			$pdf->headerFontSize = 18;
			$pdf->SetAutoPageBreak(true, 2);
			$pdf->SetDocumentTitle($settings->value('clubName') . ' Payments Received from ' . $start . ' - ' . $end);
			$pdf->SetWidths([22,
				20,
				22,
				45,
				20,
				50,
				90, ]);
			$pdf->SetHeader(['Date Received',
				'Payment Type',
				'Payment Dated',
				'Payment Number',
				'Amount',
				'Member',
				'Address', ]);
			$pdf->AddPage('L', 'Letter');
			$pdf->PrintHeader();
			$total = 0;

			foreach ($payments as $payment)
				{
				$row = $payment->toArray();
				$row[] = $payment->dateReceived;
				$row[] = $paymentTypes[$payment->paymentType ?: 0];
				$row[] = $payment->paymentDated;

				if ('' != $payment->paymentNumber)
					{
					$row[] = $payment->paymentNumber;
					}
				else
					{
					$row[] = '';
					}
				$row[] = '$' . $payment->amount;
				$total += $payment->amount;

				if ($payment->membershipId < 0)
					{
					$member = new \App\Record\Customer(0 - $payment->membershipId);
					$row[] = $member->fullName();
					$row[] = $member->address . ' ' . $member->town . ', ' . $member->zip;
					}
				else
					{
					$member = $memberTable->getMembership($payment->membershipId);

					if (! empty($member))
						{
						$row[] = $member['firstName'] . ' ' . $member['lastName'];
						$row[] = $member['address'] . ' ' . $member['town'] . ', ' . $member['zip'];
						}
					}
				$pdf->Row($row);
				}
			$pdf->SetWidths([30,
				35,
				30,
				30,
				20,
				30,
				40, ]);
			$pdf->SetFont('', 'B');
			$pdf->Row(['']);
			$pdf->Row(['']);
			$pdf->Row(['Total Checks',
				\count($payments),
				'Grand Total',
				$total, ]);
			$pdf->Output('PaymentsReceived.pdf', 'I');
			}
		}

	/**
	 * @param array<string,string> $request
	 */
	public function downloadInvoiceSummary(array $request) : void
		{
		$typeArray = $this->getTypes($request);
		$items = \App\Table\Invoice::getByDateType($start = $request['start'], $end = $request['end'], $typeArray);

		if (\count($items))
			{
			$filename = 'invoicePayments' . $start . '-' . $end . '.tsv';
			$csvWriter = new \App\Tools\CSVWriter($filename, "\t");
			$csvWriter->addHeaderRow();

			foreach ($items as $item)
				{
				$row = $item->toArray();
				unset($row['errors']);
				$csvWriter->outputRow($row);
				}
			}
		}

	/**
	 * @param array<string,string> $request
	 */
	public function downloadPaymentSummary(array $request) : void
		{
		$typeArray = $this->getTypes($request);
		$items = \App\Table\InvoiceItem::getByDateType($start = $request['start'], $end = $request['end'], $typeArray);

		if (\count($items))
			{
			$filename = 'storePayments' . $start . '-' . $end . '.tsv';
			$csvWriter = new \App\Tools\CSVWriter($filename, "\t");

			if ($request['fullDetails'])
				{
				$invoiceItem = new \App\Record\InvoiceItem();
				$fields = $invoiceItem->getFields();
				$fields['paypaltx'] = '';
				$csvWriter->outputRow(['InvoiceId', 'Item Id', 'Item Detail Id', 'Item Name', 'Description', 'DetailLine',
					'Price', 'Shipping', 'Quantity', 'Type', 'Tax', 'PayPal TX', ]);

				foreach ($items as $item)
					{
					$output = [];

					foreach ($fields as $key => $value)
						{
						$output[] = \App\Tools\TextHelper::unhtmlentities($item[$key]);
						}
					$csvWriter->outputRow($output);
					}
				}
			else
				{
				$csvWriter->outputRow(['Item Id', 'Type', 'Item Name', 'Number Sold', 'Total Price', 'Total Shipping',
					'Total Tax', ]);
				$itemsSold = [];

				foreach ($items as $invoiceItem)
					{
					$storeItemId = $invoiceItem['storeItemId'];

					if (! isset($itemsSold[$storeItemId]))
						{
						$itemsSold[$storeItemId] = new \App\Report\Accumulator();
						}
					$itemsSold[$storeItemId]->increment($invoiceItem);
					}

				foreach ($itemsSold as $key => $value)
					{
					$output = [];
					$output[] = $key;
					$output[] = $value->type;
					$output[] = $value->itemName;
					$output[] = $value->numberSold;
					$output[] = '$' . \number_format($value->totalPrice, 2, '.', '');
					$output[] = '$' . \number_format($value->totalShipping, 2, '.', '');
					$output[] = '$' . \number_format($value->totalTax, 2, '.', '');
					$csvWriter->outputRow($output);
					}
				}
			}
		}

	/**
	 * @param array<string,string> $request
	 */
	public function downloadPoints(array $request) : void
		{
		if ('outstanding' == $request['report'])
			{
			$filename = 'OutstandingPoints';
			$totalColumn = 'volunteerPoints';
			$reportName = 'Outstanding Volunteer Points as of ' . \date('F j, Y');
			$widths = [40, 40, 70, 60];
			$fields = ['firstName' => 'First Name', 'lastName' => 'Last Name', 'email' => 'email', $totalColumn => 'Outstanding Points'];
			$sort = 'lastName,firstName';

			if (empty($request['sort']))
				{
				$sort = $totalColumn . ' desc,' . $sort;
				}
			$result = \App\Table\Member::outstandingPoints($sort);
			}
		else
			{
			$filename = 'PointsUsed';
			$totalColumn = 'pointsUsed';
			$start = $request['startDate'];
			$end = $request['endDate'];
			$reportName = 'Points Redeemed ';

			if ($start && $end)
				{
				$reportName .= 'from ' . \App\Tools\Date::formatString('F j, Y', $start) . ' to ' . \App\Tools\Date::formatString('F j, Y', $end);
				}
			elseif ($start)
				{
				$reportName .= 'from ' . \App\Tools\Date::formatString('F j, Y', $start);
				}
			elseif ($end)
				{
				$reportName .= 'until ' . \App\Tools\Date::formatString('F j, Y', $end);
				}
			$widths = [40, 40, 70, 30, 60];
			$fields = ['firstName' => 'First Name', 'lastName' => 'Last Name', 'email' => 'email',
				'invoiceId' => 'Invoice Id', $totalColumn => 'Points Redeemed', ];
			$sort = 'lastName,firstName';

			if (empty($request['sort']))
				{
				$sort = $totalColumn . ' desc,' . $sort;
				}
			$result = \App\Table\Invoice::pointsUsed($start, $end, $sort);
			}

		if ('CSV' == $request['downloadType'])
			{
			$csvWriter = new \App\Tools\CSVWriter($filename . '.csv');
			$csvWriter->outputRow($fields);


			foreach ($result as $member)
				{
				$row = [];

				foreach ($fields as $field => $header)
					{
					$row[] = \App\Tools\TextHelper::unhtmlentities($member[$field]);
					}
				$csvWriter->outputRow($row);
				}
			}
		else
			{
			$pdf = new \PDF_MC_Table();
			$pdf->SetDisplayMode('fullpage');
			$pdf->SetFont('Arial', '', 10);
			$pdf->setNoLines(true);
			$pdf->headerFontSize = 18;
			$pdf->SetAutoPageBreak(true, 2);
			$settings = new \App\Table\Setting();
			$pdf->SetDocumentTitle($settings->value('clubName') . ' ' . $reportName);
			$pdf->SetWidths($widths);
			$pdf->SetHeader(\array_values($fields));
			$pdf->AddPage('L', 'Letter');
			$pdf->PrintHeader();
			$total = 0;

			foreach ($result as $member)
				{
				$total += $member[$totalColumn];
				$row = [];

				foreach ($fields as $field => $header)
					{
					$row[] = \App\Tools\TextHelper::unhtmlentities($member[$field]);
					}
				$pdf->Row($row);
				}

			$row = [];
			$label = 'Total Points';

			foreach ($fields as $field => $header)
				{
				if ($field == $totalColumn)
					{
					$row[] = $total;
					}
				else
					{
					$row[] = $label;
					$label = '';
					}
				}
			$pdf->Row($row);
			$pdf->Output($filename . '.pdf', 'I');
			}
		}

	/**
	 * @param array<string,string> $request
	 */
	public function downloadTaxesCollected(array $request) : void
		{
		$taxes = \App\Table\Invoice::getTaxes($start = $request['start'], $end = $request['end']);

		if (\count($taxes))
			{
			$filename = 'taxesCollected' . \App\Tools\Date::formatString('ymd', $start) . '-' . \App\Tools\Date::formatString('ymd', $end) . '.csv';
			$csvWriter = new \App\Tools\CSVWriter($filename);
			$csvWriter->outputRow(['Order Date', 'Gross Sale', 'Shipping', 'Sales Tax', 'Total Sale', 'PayPal Paid',
				'Volunteer Points', 'ZipCode', 'State', ]);
			$customerModel = new \App\Model\Customer();

			foreach ($taxes as $invoice)
				{
				$member = $customerModel->read($invoice->memberId);
				$row = [$invoice['orderDate'],
					'$' . \number_format($invoice->totalPrice, 2, '.', ''),
					'$' . \number_format($invoice->totalShipping, 2, '.', ''),
					'$' . \number_format($invoice->totalTax, 2, '.', ''),
					'$' . \number_format($invoice->pointsUsed + $invoice->paypalPaid, 2, '.', ''),
					'$' . \number_format($invoice->paypalPaid, 2, '.', ''), \number_format($invoice->pointsUsed, 2, '.', ''),
					$member->zip,
					$member->state,
				];

				$csvWriter->outputRow($row);
				}
			}
		}

	/**
	 * @param array<string,string> $request
	 *
	 * @return array<int>
	 */
	private function getTypes(array $request) : array
		{
		$typeArray = [];

		if ($request['store'] ?? 0)
			{
			$typeArray[] = \App\Model\Cart::TYPE_STORE;
			}

		if ($request['PE'] ?? 0)
			{
			$typeArray[] = \App\Model\Cart::TYPE_GA;
			}

		if ($request['membership'] ?? 0)
			{
			$typeArray[] = \App\Model\Cart::TYPE_MEMBERSHIP;
			}

		if ($request['discount'] ?? 0)
			{
			$typeArray[] = \App\Model\Cart::TYPE_DISCOUNT_CODE;
			}

		if ($request['club'] ?? 0)
			{
			$typeArray[] = \App\Model\Cart::TYPE_EVENT;
			}

		return $typeArray;
		}
	}
