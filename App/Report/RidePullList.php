<?php

namespace App\Report;

class RidePullList
	{
	public function download(string $date) : void
		{
		$pdf = $this->generate($date);
		$pdf->Output($this->getFileName($date), 'I');
		}

	public function generate(string $date) : \PDF_MC_Table
		{
		$rideTable = new \App\Table\Ride();
		$rideTable->setInventoryBySignup($date);

		$pdf = new \PDF_MC_Table();
		$pdf->SetDisplayMode('fullpage');
		$pdf->SetFont('Arial', '', 10);
		$pdf->setNoLines(true);
		$pdf->headerFontSize = 20;
		$pdf->AddPage('L', 'Letter');
		$settings = new \App\Table\Setting();
		$pdf->SetDocumentTitle($settings->value('clubAbbrev') . ' Ride Pull Report for rides on ' . $date);
		$pdf->SetAutoPageBreak(true, 2);
		$pdf->SetWidths([80,
			40,
			60,
			60,
			20,
		]);
		$pdf->SetHeader(['Ride',
			'Name',
			'Desciption',
			'Detail',
			'Quantity', ]);
		$pdf->SetAligns(['L',
			'L',
			'L',
			'L', 'C']);
		$pdf->PrintHeader();

		foreach ($rideTable->getArrayCursor() as $row)
			{
			$pdf->Row(\array_values($row));
			}

		return $pdf;
		}

	public function getFileName(string $date) : string
		{
		$settingTable = new \App\Table\Setting();

		return $settingTable->value('clubAbbrev') . "RidePullReport-{$date}.pdf";
		}
	}
