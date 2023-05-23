<?php

namespace App\Report;

class PullList
	{
	public function download(\PHPFUI\ORM\ArrayCursor $itemResult) : void
		{
		$pdf = $this->generate($itemResult);
		$pdf->Output($this->getFileName(), 'I');
		}

	public function generate(\PHPFUI\ORM\ArrayCursor $itemResult) : \PDF_MC_Table
		{
		$items = [];

		foreach ($itemResult as $item)
			{
			$key = $item['storeItemId'] . '`' . $item['storeItemDetailId'] . '`' . $item['title'] . '`' . $item['detailLine'];

			if (! isset($items[$key]))
				{
				$items[$key] = $item['quantity'];
				}
			else
				{
				$items[$key] += $item['quantity'];
				}
			}
		\ksort($items);
		$pdf = new \PDF_MC_Table();
		$pdf->SetDisplayMode('fullpage');
		$pdf->SetFont('Arial', '', 10);
		$pdf->setNoLines(true);
		$pdf->headerFontSize = 20;
		$pdf->AddPage('L', 'Letter');
		$settings = new \App\Table\Setting();
		$pdf->SetDocumentTitle($abbrev = $settings->value('clubAbbrev') . ' Store Pull Report Printed On ' . \App\Tools\Date::todayString());
		$pdf->SetAutoPageBreak(true, 2);
		$pdf->SetWidths([30,
			// item number
			20,
			// Count
			100,
			// Item
			100,
			// Size
		]);
		$pdf->SetHeader(['Item Number',
			'Count',
			'Item',
			'Size', ]);
		$pdf->SetAligns(['C',
			'C',
			'L',
			'L', ]);
		$pdf->PrintHeader();

		foreach ($items as $key => $value)
			{
			$keys = \explode('`', $key);

			$storeItemDetail = new \App\Record\StoreItemDetail(['storeItemId' => $keys[0],
				'storeItemDetailId' => $keys[1], ]);
			$storeItemDetail->reload();

			if ($storeItemDetail->loaded())
				{
				$pdf->Row([$keys[0],
					$value,
					$storeItemDetail->storeItem->title,
					$storeItemDetail->detailLine]);
				}
			else
				{
				$pdf->Row([$keys[0],
					$value,
					$keys[2],
					$keys[3]]);
				}
			}

		return $pdf;
		}

	public function getFileName() : string
		{
		$now = \date('Y-m-d');
		$settingTable = new \App\Table\Setting();

		return $settingTable->value('clubAbbrev') . "PullReport-{$now}.pdf";
		}
	}
