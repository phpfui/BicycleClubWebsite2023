<?php

namespace App\Report;

class Roster
	{
	/**
	 * @param array<string,string> $parameters
	 */
	public function download(array $parameters) : void
		{
		$settings = new \App\Table\Setting();
		$clubAbbrev = $settings->value('clubAbbrev');

		$csv = ($parameters['format'] ?? 'PDF') == 'CSV';

		$csvWriter = null;
		$pdf = null;

		// includeRides
		$includeRides = (bool)$parameters['includeRides'];

		if ($includeRides)
			{
			$fields = ['Name', 'Ride', 'Cat', 'Leader', 'Date', 'Signup', 'Attended'];
			$widths = [
				45, // Name
				90, // ride
				15, // category
				40, // leader
				22, // date
				30, // signup
				30, // status
			];
			}
		else
			{
			$widths = [
				45, // Name
				40, // Address
				40, // Town
				12, // State
				60, // email
				24, // Cell
				21, // Joined
				21, // Lapsed
			];
			$fields = ['Name', 'Address', 'Town', 'State', 'email', 'Cell', 'Joined', 'Expires', ];
			}

		if ($csv)
			{
			$fileName = "{$clubAbbrev}_Roster{$parameters['startDate']}-{$parameters['endDate']}.csv";
			$csvWriter = new \App\Tools\CSV\FileWriter($fileName);
			$csvWriter->addHeaderRow();
			}
		else
			{
			$pdf = new \PDF_MC_Table();
			$pdf->SetDisplayMode('fullpage');
			$pdf->SetFont('Arial', '', 10);
			$pdf->setNoLines(true);
			$pdf->headerFontSize = 18;
			$pdf->SetAutoPageBreak(true, 2);
			$pdf->SetWidths($widths);
			$pdf->SetHeader($fields);
			$pdf->AddPage('L', 'Letter');
			$pdf->SetDocumentTitle("{$clubAbbrev} Membership Roster {$parameters['startDate']} > {$parameters['endDate']} Printed On " . \App\Tools\Date::todayString());
			$pdf->PrintHeader();
			}

		$membershipTable = new \App\Table\Membership();
		$membershipTable->addJoin('member', 'membershipId');
		$membershipTable->addOrderBy('lastName')->addOrderBy('firstName');
		$condition = new \PHPFUI\ORM\Condition();

		if ('all' != $parameters['reportType'])
			{
			$condition->and($parameters['reportType'], $parameters['startDate'], new \PHPFUI\ORM\Operator\GreaterThanEqual());
			$condition->and($parameters['reportType'], $parameters['endDate'], new \PHPFUI\ORM\Operator\LessThanEqual());
			}
		$membershipTable->setWhere($condition);

		$attended = \App\Table\RideSignup::getAttendedStatus();
		$status = \App\Table\RideSignup::getRiderStatus();

		// includeRides
		foreach ($membershipTable->getArrayCursor() as $member)
			{
			if ($includeRides)
				{
				$name = "{$member['firstName']} {$member['lastName']}";
				$rideTable = new \App\Table\Ride();
				$rideTable->addJoin('rideSignup', 'rideId');
				$condition = new \PHPFUI\ORM\Condition('rideDate', $parameters['startDate'], new \PHPFUI\ORM\Operator\GreaterThanEqual());
				$condition->and('rideDate', $parameters['endDate'], new \PHPFUI\ORM\Operator\LessThanEqual());
				$condition->and('rideSignup.memberId', $member['memberId']);
				$rideTable->setWhere($condition);
				$cursor = $rideTable->getDataObjectCursor();

				if (! \count($cursor))
					{
					$row = [$name];

					if (! $csv)
						{
						$pdf->Row($row);
						}
					else
						{
						$csvWriter->outputRow($row);
						}
					}

				foreach ($rideTable->getDataObjectCursor() as $ride)
					{
					$row = [$name, $ride->title, $ride->pace->pace, $ride->member->fullName(), $ride->rideDate, $status[$ride->status] ?? '', $attended[$ride->attended] ?? ''];

					if (! $csv)
						{
						$pdf->Row($row);
						$name = '';
						}
					else
						{
						$csvWriter->outputRow($row);
						}
					}
				}
			else
				{
				if ($csv)
					{
					foreach ($member as $key => $value)
						{
						if (\str_contains($key, 'password'))
							{
							unset($member['key']);
							}
						}
					$csvWriter->outputRow($member);
					}
				else
					{
					$row = ["{$member['firstName']} {$member['lastName']}", $member['address'], $member['town'], $member['state'], $member['email'], $member['cellPhone'], $member['joined'], $member['expires'], ];
					$pdf->Row($row);
					}
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
