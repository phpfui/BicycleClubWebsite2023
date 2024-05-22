<?php

namespace App\Report;

class GASignInSheets
	{
	final public const CSV = 2;

	final public const PAGED = 1;

	final public const SINGLE = 0;

	public function __construct(private readonly \App\Record\GaEvent $event, private readonly int $type, private readonly int $paid, private int $tagNumber = 0)
		{
		}

	public function generate() : void
		{
		$gaRiderTable = new \App\Table\GaRider();
		$riders = $gaRiderTable->getRiderCursor($this->event, $this->paid);

		if (self::CSV == $this->type)
			{
			$csvWriter = new \App\Tools\CSV\FileWriter("GARegistrants{$this->event->gaEventId}.csv");

			foreach ($riders as $rider)
				{
				$csvWriter->outputRow($this->processRider($rider));
				}
			}
		else
			{
			$pdf = new \PDF_MC_Table();
			$pdf->SetMargins(5, 5, 5);
			$pdf->SetDisplayMode('fullpage');
			$pdf->AddFont('Futura', '', 'futura.php');
			$pdf->AddFont('Futura', 'B', 'futura.php');
			$pdf->SetFont('Futura', '', 12);
			$pdf->SetFillLines(1);
			$pdf->SetFillColor(225);
			$pdf->SetAutoPageBreak(true, 2);
			$widths = $headers = [];

			if ($this->tagNumber)
				{
				$headers[] = 'Tag';
				$widths[] = 15;
				}

			$headers[] = 'Last';
			$widths[] = 40;
			$headers[] = 'First';
			$widths[] = 40;
			$headers[] = 'Emer Phone';
			$widths[] = 33;
			$headers[] = 'Emer Contact';
			$widths[] = 50;

			$gaOptionTable = new \App\Table\GaOption();
			$whereCondition = new \PHPFUI\ORM\Condition('gaeventId', $this->event->gaEventId);
			$whereCondition->and(new \PHPFUI\ORM\Condition('csvField', '', new \PHPFUI\ORM\Operator\GreaterThan()));
			$gaOptionTable->setWhere($whereCondition);
			$gaOptionTable->addOrderBy('ordering');
			$options = $gaOptionTable->getRecordCursor();

			foreach ($options as $option)
				{
				$headers[] = $option->csvField;
				$widths[] = 30;
				}

			$pdf->SetWidths($widths);
			$pdf->SetHeader($headers);

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

				$row = [];

				if ($this->tagNumber)
					{
					$row[] = $this->tagNumber++;
					}
				$rider->contactPhone = \App\Tools\TextHelper::formatPhone($rider->contactPhone);
				$row[] = $riderArray['lastName'];
				$row[] = $riderArray['firstName'];
				$row[] = $riderArray['contactPhone'];
				$row[] = $riderArray['contact'];

				foreach ($options as $option)
					{
					$row[] = $riderArray[$option->csvField];
					}

				$pdf->Row($row);
				$pdf->SetDocumentTitle($title . ' (continued)');
				}
			$pdf->Output('I', "Preregistration-{$this->event->gaEventId}.pdf");
			}
		}

	/**
	 * @return array<string,string>
	 */
	private function processRider(\PHPFUI\ORM\DataObject $rider) : array
		{
		$retVal = $rider->toArray();

		foreach ($retVal as $key => $field)
			{
			$retVal[$key] = \App\Tools\TextHelper::unhtmlentities($field);
			}

		return $retVal;
		}
	}
