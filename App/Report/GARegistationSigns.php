<?php

namespace App\Report;

class GARegistationSigns
	{
	private \FPDF $pdf;

	public function __construct(private readonly \App\Record\GaEvent $event, private readonly int $count, private readonly int $paid = 1)
		{
		}

	public function generate() : void
		{
		$this->pdf = new \FPDF('L', 'mm', 'Letter');
		$this->pdf->SetDisplayMode('fullpage');
		$gaRiderTable = new \App\Table\GaRider();

		$riders = $gaRiderTable->getRiderCursor($this->event, $this->paid);
		$totalRiders = \count($riders);
		$perSign = \round($totalRiders / $this->count);
		$first = $last = '';
		$count = 0;
		$printed = 0;

		foreach ($riders as $rider)
			{
			$printed++;

			if (! $first)
				{
				$first = \ucfirst($rider->lastName[0]);
				}
			$currentLast = \ucfirst(\chr(\ord($rider->lastName[0]) - 1));

			if (++$count >= $perSign && $currentLast != $last)
				{
				if ($printed == $totalRiders)
					{
					$currentLast = \ucfirst($rider->lastName[0]);
					}
				$this->addPage($first, $currentLast);
				$count = 0;
				$first = '';
				}
			$last = \ucfirst($rider->lastName[0]);
			}

		if ($first)
			{
			$this->addPage($first, $last);
			}
		$this->pdf->Output('I', 'RegistrationSigns' . $this->event->gaEventId . "-{$this->count}Lines.pdf");
		}

	private function addPage(string $first, string $last) : void
		{
		$first = \substr($first, 0, 3);
		$last = \substr($last, 0, 3);
		$bigPoint = 250;
		$lineHeight = 80;
		$smallPoint = 150;
		$namePoint = $smallPoint - 40;
		$this->pdf->AddPage('L');
		$this->pdf->SetFont('Arial', 'B', 10);
		$this->pdf->Write(10, "\n");
		$this->pdf->SetFont('Arial', 'B', $namePoint);
		$this->pdf->Write($lineHeight, 'Last   ');
		$this->pdf->SetFont('Arial', 'B', $bigPoint);
		$this->pdf->Write($lineHeight, $first);
		$this->pdf->SetFont('Arial', 'B', $smallPoint);
		$this->pdf->Write($lineHeight, "->\n");
		$this->pdf->SetFont('Arial', 'B', 10);
		$this->pdf->Write(10, "\n");
		$this->pdf->SetFont('Arial', 'B', $namePoint);
		$this->pdf->Write($lineHeight, 'Name ');
		$this->pdf->SetFont('Arial', 'B', $smallPoint);
		$this->pdf->Write($lineHeight, '->');
		$this->pdf->SetFont('Arial', 'B', $bigPoint);
		$this->pdf->Write($lineHeight, $last);
		}
	}
