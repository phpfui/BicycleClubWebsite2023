<?php

namespace App\Report;

class GASignInSheets
	{
	final public const CSV = 2;

	final public const PAGED = 1;

	final public const SINGLE = 0;

	public function __construct(private readonly \App\Record\GaEvent $event, private readonly int $type, private int $tagNumber = 1)
		{
		}

	public function generate() : void
		{
		$gaRiderTable = new \App\Table\GaRider();
		$riders = $gaRiderTable->getPaidRiderCursor($this->event);

		if (self::CSV == $this->type)
			{
			$csvWriter = new \App\Tools\CSV\FileWriter("GARegistrants{$this->event->gaEventId}.csv");
			$csvWriter->addHeaderRow();

			foreach ($riders as $rider)
				{
				$csvWriter->outputRow($this->processRider($rider));
				}
			}
		else
			{
			$pdf = new \PDF_MC_Table();
			$pdf->SetDisplayMode('fullpage');
			$pdf->AddFont('Futura', '', 'futura.php');
			$pdf->AddFont('Futura', 'B', 'futura.php');
			$pdf->SetFont('Futura', '', 14);
			$pdf->SetFillLines(1);
			$pdf->SetFillColor(225);
			$pdf->SetAutoPageBreak(true, 2);
			$pdf->SetWidths([22, // tag number
				60, // lastName
				60, // firstName
				45, // Emergency Phone
				65, // Emergency Contact
				5, ]); // Incentive ID
			$pdf->SetHeader(['Tag',
				'Last',
				'First',
				'Emer Phone',
				'Emergency Contact',
				'', ]);

			$title = $titleBase = $this->event->title;
			$lastName = $riders->current()['lastName'];
			$pageChar = $lastName[0];

			if (self::PAGED == $this->type)
				{
				$title = $pageChar . ' - ' . $titleBase;
				}
			$pdf->SetDocumentTitle($title);
			$pdf->AddPage('L', 'Letter');
			$pdf->PrintHeader();

			foreach ($riders as $rider)
				{
				$riderArray = $this->processRider($rider);

				if (self::PAGED == $this->type)
					{
					$firstChar = $riderArray['lastName'][0];
					$firstChar = \strtoupper((string)$firstChar);

					if ($firstChar != $pageChar)
						{
						$pageChar = $riderArray['lastName'][0];
						$title = $pageChar . ' - ' . $titleBase;
						$pdf->SetDocumentTitle($title);
						$pdf->AddPage('L', 'Letter');
						$pdf->PrintHeader();
						}
					}
				$tag = $this->tagNumber ? $this->tagNumber++ : '';
				$rider->contactPhone = \App\Tools\TextHelper::formatPhone($rider->contactPhone);

				$pdf->Row([$tag, $riderArray['lastName'],
					$riderArray['firstName'],
					$riderArray['contactPhone'],
					$riderArray['contact'],
					-1 == $rider->gaIncentiveId ? '*' : '', ]);
				$pdf->SetDocumentTitle($title . ' (continued)');
				}
			$pdf->Output('I', "Preregistration-{$this->event->gaEventId}.pdf");
			}
		}

	/**
	 * @return array<string,string>
	 */
	private function processRider(\App\Record\GaRider $rider) : array
		{
		$retVal = $rider->toArray();

		foreach ($retVal as $key => $field)
			{
			$retVal[$key] = \App\Tools\TextHelper::unhtmlentities($field);
			}

		return $retVal;
		}
	}
