<?php

namespace App\Report;

class Roster
	{
	/**
	 * @param array<string,string> $parameters
	 */
	public function download(array $parameters) : void
		{
		$membershipTable = new \App\Table\Membership();
		$settings = new \App\Table\Setting();
		$clubAbbrev = $settings->value('clubAbbrev');

		$csv = ($parameters['format'] ?? 'PDF') == 'CSV';

		$csvWriter = null;
		$pdf = null;
		$fields = ['Name', 'Town', 'State', 'email', 'Cell', 'Joined', 'Expires', ];

		if ($csv)
			{
			$fileName = "{$clubAbbrev}_Roster{$parameters['startDate']}-{$parameters['endDate']}.csv";
			$csvWriter = new \App\Tools\CSV\FileWriter($fileName);
			$csvWriter->addHeaderRow();
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
			$pdf->SetWidths([
				55, // Name
				40, // Town
				12, // State
				70, // email
				30, // Cell
				30, // Joined
				30, // Lapsed
			]);
			$pdf->SetHeader($fields);
			$pdf->AddPage('L', 'Letter');
			$pdf->SetDocumentTitle("{$clubAbbrev} Membership Roster {$parameters['startDate']} > {$parameters['endDate']} Printed On " . \App\Tools\Date::todayString());
			$pdf->PrintHeader();
			}

		$members = $membershipTable->getMembershipsActive($parameters['startDate'], $parameters['endDate']);

		foreach ($members as $member)
			{
			$row = ["{$member['firstName']} {$member['lastName']}", $member['town'], $member['state'], $member['email'], $member['cellPhone'], $member['joined'], $member['expires'], ];

			if ($csv)
				{
				$csvWriter->outputRow($row);
				}
			else
				{
				$pdf->Row($row);
				}
			}

		if (! $csv)
			{
			$now = \date('Y-m-d');
			$fileName = "{$clubAbbrev}_Roster_{$parameters['startDate']}-{$parameters['endDate']}.pdf";
			$pdf->Output($fileName, 'I');
			}
		}
	}
