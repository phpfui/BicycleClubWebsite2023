<?php

namespace App\Report;

class GARegistationSigns
	{
	private $pdf;

	public function __construct(private readonly \App\Record\GaEvent $event, private readonly int $count)
		{
		}

	public function generate() : void
		{
		$this->pdf = new \FPDF('L', 'mm', 'Letter');
		$this->pdf->SetDisplayMode('fullpage');
		$gaRiderTable = new \App\Table\GaRider();
		$riders = $gaRiderTable->getPaidRiderCursor($this->event);
		$perSign = \round(\count($riders) / $this->count);
		$first = $last = '';
		$count = 0;

		foreach ($riders as $rider)
			{
			if (! $first)
				{
				$first = \ucfirst(\substr((string)$rider->lastName, 0, 3));
				}
			$currentLast = \ucfirst(\substr((string)$rider->lastName, 0, 3));

			if (++$count >= $perSign && $currentLast != $last)
				{
				$this->addPage($first, $currentLast);
				$count = 0;
				$first = '';
				}
			$last = \ucfirst(\substr((string)$rider->lastName, 0, 3));
			}

		if ($first)
			{
			$this->addPage($first, $last);
			}
		$this->pdf->Output('I', 'RegistrationSigns' . $this->event->gaEventId . '.pdf');
		}

	private function addPage(string $first, string $last) : void
		{
		$first = \substr($first, 0, 3);
		$last = \substr($last, 0, 3);
		$bigPoint = 250;
		$lineHeight = 80;
		$smallPoint = 150;
		$this->pdf->AddPage('L');
		$this->pdf->SetFont('Arial', 'B', 10);
		$this->pdf->Write(10, "\n");
		$this->pdf->SetFont('Arial', 'B', $bigPoint);
		$this->pdf->Write($lineHeight, $first);
		$this->pdf->SetFont('Arial', 'B', $smallPoint);
		$this->pdf->Write($lineHeight, "=>\n");
		$this->pdf->SetFont('Arial', 'B', 10);
		$this->pdf->Write(10, "\n");
		$this->pdf->SetFont('Arial', 'B', $smallPoint);
		$this->pdf->Write($lineHeight, '=>');
		$this->pdf->SetFont('Arial', 'B', $bigPoint);
		$this->pdf->Write($lineHeight, $last);
		}
	}
