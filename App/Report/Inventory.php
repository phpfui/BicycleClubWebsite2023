<?php

namespace App\Report;

class Inventory
	{
	public function download(array $parameters) : void
		{
		$pdf = new \PDF_MC_Table();
		$pdf->SetDisplayMode('fullpage');
		$pdf->SetFont('Arial', '', 10);
		$pdf->setNoLines(true);
		$pdf->headerFontSize = 18;
		$pdf->SetAutoPageBreak(true, 2);
		$pdf->SetWidths([25,
			// item number
			70,
			// title
			80,
			// detail
			20,
			// quantity
			20,
			// price
			20,
			// total
			20,
			// shipping
		]);
		$pdf->SetHeader(['Item Number',
			'Title',
			'Detail',
			'Quantity',
			'Price',
			'Total',
			'Shipping', ]);
		$pdf->SetAligns(['C',
			'L',
			'L',
			'C',
			'R',
			'R',
			'R', ]);
		$title = ' Complete Inventory';
		$type = $parameters['type'] ?? '';

		if ('S' == $type)
			{
			$title = ' In Stock Inventory';
			}
		elseif ('O' == $type)
			{
			$title = ' Out Of Stock Inventory';
			}
		$result = \App\Table\StoreItem::byTitle(1);
		$grandTotal = 0;
		$settings = new \App\Table\Setting();

		if (\count($result))
			{
			$pdf->AddPage('L', 'Letter');
			$pdf->SetDocumentTitle($settings->value('clubAbbrev') . $title . ' Report Printed On ' . \App\Tools\Date::todayString());
			$pdf->PrintHeader();

			foreach ($result as $item)
				{
				if ('S' == $type)
					{
					$sid = \App\Table\StoreItemDetail::getInStock((int)$item['storeItemId']);
					}
				elseif ('O' == $type)
					{
					$sid = \App\Table\StoreItemDetail::getOutOfStock((int)$item['storeItemId']);
					}
				else
					{
					$sid = \App\Table\StoreItemDetail::getAllStock((int)$item['storeItemId']);
					}

				if ($rows = \count($sid))
					{
					$detail = $sid->current();
					$total = (int)$detail['quantity'] * (float)$item['price'];
					$grandTotal += $total;

					if ($item['shipping'] > 0)
						{
						$shipping = '$' . $item['shipping'];
						}
					elseif ($item['noShipping'])
						{
						$shipping = 'None';
						}
					else
						{
						$shipping = 'Free';
						}
					$pdf->Row([$item['storeItemId'] . '-' . $detail['storeItemDetailId'],
						\App\Tools\TextHelper::unhtmlentities($item['title']),
						\App\Tools\TextHelper::unhtmlentities($detail['detailLine']),
						$detail['quantity'],
						'$' . $item['price'],
						'$' . $total,
						$shipping, ]);

					while (--$rows)
						{
						$sid->next();
						$detail = $sid->current();
						$total = (int)$detail['quantity'] * (float)$item['price'];
						$grandTotal += $total;
						$pdf->Row([$item['storeItemId'] . '-' . $detail['storeItemDetailId'],
							'',
							\App\Tools\TextHelper::unhtmlentities($detail['detailLine']),
							$detail['quantity'],
							'$' . $item['price'],
							'$' . $total,
							'', ]);
						}
					$pdf->Row(['',
						'',
						'',
						'',
						'',
						'',
						'', ]);
					}
				}
			$pdf->Row(['',
				'',
				'',
				'',
				'Grand Total',
				'$' . $grandTotal,
				'', ]);
			}
		$now = \date('Y-m-d');
		$pdf->Output($settings->value('clubAbbrev') . "InventoryPrint-{$now}.pdf", 'I');
		}
	}
