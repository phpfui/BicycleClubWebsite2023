<?php

namespace App\Report;

class Label
	{
	private string $name;

	private readonly \App\Table\Setting $settingTable;

	public function __construct()
		{
		$this->settingTable = new \App\Table\Setting();
		}

	/**
	 * @param array<string,string> $mailTo
	 */
	public function download(\App\Record\Invoice $invoice, array $mailTo) : void
		{
		$pdf = $this->generate($invoice, $mailTo);
		$pdf->Output($this->getFileName(), 'I');
		}

	public function getFileName() : string
		{
		return $this->settingTable->value('clubAbbrev') . "Label-{$this->name}.pdf";
		}

	/**
	 * @param array<string,string> $mailTo
	 */
	private function generate(\App\Record\Invoice $invoice, array $mailTo) : \FPDF
		{
		$this->name = \App\Tools\TextHelper::unhtmlentities($mailTo['firstName']) . ' ' . \App\Tools\TextHelper::unhtmlentities($mailTo['lastName']);
		$member = \App\Model\Session::getSignedInMember();
		$postageType = 'FIRST CLASS MAIL';
		$postage = 0;
		$pdf = new \FPDF('L', 'in', 'Letter');
		$pdf->SetTitle('Mailing Label');
		$pdf->AddPage('L');
		$pdf->SetMargins(.7, 1);
		$pdf->SetLineWidth(.016);
		$x = $pdf->GetX();
		$y = $pdf->GetY();
		$pdf->Cell(4, 6, '', 1);
		$pdf->SetXY($x + 2.5, $y + .1);
		$pdf->SetFont('Arial', '', 10);
		$pdf->SetXY($x, $y);
		$pdf->SetFont('Arial', 'B', 70);
		$pdf->Cell(1, 1, $postageType[0], 1, 2, 'C');
		$pdf->SetFont('Arial', 'B', 20);
		$pdf->Cell(4, .5, 'USPS ' . $postageType, 1, 2, 'C');
		$pdf->SetFont('Arial', '', 10);
		$pdf->Cell(4, .16, ' ', 0, 2);
		$pdf->Cell(4, .16, '  ' . $this->settingTable->value('clubName'), 0, 2);
		$pdf->Cell(4, .16, '  ' . \App\Tools\TextHelper::unhtmlentities($member['firstName']) . ' ' . \App\Tools\TextHelper::unhtmlentities($member['lastName']), 0, 2);
		$pdf->Cell(4, .16, '  ' . \App\Tools\TextHelper::unhtmlentities($member['address']), 0, 2);
		$pdf->Cell(4, .16, '  ' . \App\Tools\TextHelper::unhtmlentities($member['town']) . ', ' . $member['state'] . ' ' . $member['zip'], 0, 2);
		$pdf->Cell(4, .3, ' ', 0, 2);
		$pdf->SetFont('Arial', 'B', 14);
		$pdf->Cell(4, .175, '  Return Service Requested', 0, 2);
		$pdf->Cell(4, .5, ' ', 0, 2);
		$pdf->SetFont('Arial', '', 16);
		$pdf->SetX($x + .5);
		$pdf->Cell(4, .25, '  ' . $this->name, 0, 2);
		$pdf->Cell(4, .25, '  ' . \App\Tools\TextHelper::unhtmlentities($mailTo['address']), 0, 2);
		$pdf->Cell(4, .25, '  ' . \App\Tools\TextHelper::unhtmlentities($mailTo['town']) . ', ' . $mailTo['state'], 0, 2);
		$pdf->Cell(4, .25, '  ' . $mailTo['zip'], 0, 2);
		$x = 5.5;
		$pdf->Line($x, $y, $x, $y + 7.5);
		$x += .5;
		$pdf->SetXY($x, $y);
		$pdf->Cell(4, .25, 'Packing List', 0, 2, 'C');
		$pdf->SetFont('Arial', '', 12);
		$pdf->Cell(4, .25, 'Shipped on: ' . \App\Tools\Date::todayString(), 0, 2);
		$pdf->Cell(4, .25, 'Ship to:', 0, 2);
		$pdf->SetFont('Arial', '', 12);
		$pdf->Cell(4, .16, '  ' . $this->name, 0, 2);
		$pdf->Cell(4, .16, '  ' . \App\Tools\TextHelper::unhtmlentities($mailTo['address']), 0, 2);
		$pdf->Cell(4, .16, '  ' . \App\Tools\TextHelper::unhtmlentities($mailTo['town']) . ', ' . $mailTo['state'] . ' ' . $mailTo['zip'], 0, 2);
		$pdf->Cell(4, .16, ' ', 0, 2);
		$xDesc = $x;
		$xSize = $x + 2;
		$xQuant = $x + 4;
		$pdf->SetFont('Arial', 'B', 14);
		$pdf->Cell(2, .16, 'Item', 0, 0);
		$pdf->Cell(2, .16, 'Size', 0, 0);
		$pdf->Cell(1, .16, 'Count', 0, 0);
		$y = $pdf->GetY() + .2;
		$pdf->Line($xDesc, $y, $xDesc + 4.6, $y);
		$pdf->SetFont('Arial', '', 10);
		$pdf->SetY($y - .1);

		foreach ($invoice->InvoiceItemChildren as $invoiceItem)
			{
			$y = $pdf->GetY() + .15;
			$pdf->SetXY($xDesc, $y);
			$pdf->MultiCell(2, .15, \App\Tools\TextHelper::unhtmlentities($invoiceItem->title), 0, 'L');
			$pdf->SetXY($xSize, $y);
			$pdf->MultiCell(2, .15, \App\Tools\TextHelper::unhtmlentities($invoiceItem->detailLine), 0, 'L');
			$pdf->SetXY($xQuant, $y);
			$pdf->MultiCell(.7, .15, $invoiceItem->quantity, 0, 'C');
			}

		return $pdf;
		}
	}
