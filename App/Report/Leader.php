<?php

namespace App\Report;

class Leader extends \PDF_MC_Table
	{
	private array $blankRow = [];

	private array $columnAlign = [];

	private array $columnWidths = [];

	private int $currentYear;

	private int $firstYear;

	private array $headerTitles = [];

	private array $initRides = [];

	private string $lastLead = '';

	private int $lastLeader = 0;

	private array $leaders = [];

	private string $nextLead = '';

	private array $ridesByYear;

	private array $rows = [];

	private static string $sort;

	public function __construct(private readonly string $reportName)
		{
		parent::__construct();
		}

	public static function cmp(array $a, array $b) : int
		{
		$lhs = $a[self::$sort];
		$rhs = $b[self::$sort];

  return $lhs <=> $rhs;
		}

	public function generateClassic(array $parameters) : void
		{
		self::$sort = $parameters['sort'];
		$this->currentYear = (int)\App\Tools\Date::year(\App\Tools\Date::today());
		$this->firstYear = $this->currentYear - 4;
		$this->addHeader('cat', 'Categories', 'L', 30);
		$this->addHeader('leader', 'Leader', 'L', 45);
		$this->addHeader('phone', 'Phone', 'R', 25);
		$this->addHeader('email', 'email', 'L', 55);

		for ($i = 0; $i < 5; $i++)
			{
			$year = $this->firstYear + $i;
			$this->addHeader((string)$year, (string)$year, 'C', 10);
			}
		$this->addHeader('last5', 'Last 5', 'C', 15);
		$this->addHeader('lastRide', 'Last Led', 'L', 23);
		$this->addHeader('nextRide', 'Next Lead', 'L', 23);
		$categories = empty($parameters['categories']) ? [] : $parameters['categories'];
		$paceTable = new \App\Table\Pace();
		$paces = $paceTable->getPacesForCategories($categories);
		$today = \App\Tools\Date::todayString();
		$startDate = \gregoriantojd(1, 1, $this->firstYear);
		$endDate = \gregoriantojd(12, 31, $this->currentYear);
		$rideTable = new \App\Table\Ride();
		$rides = $rideTable->getLeadersRides($paces, \App\Tools\Date::toString($startDate), \App\Tools\Date::toString($endDate));
		$this->initRides = [];

		for ($year = $this->firstYear; $year <= $this->currentYear; ++$year)
			{
			$this->initRides[$year] = 0;
			}
		$this->nextLead = '';
		$this->lastLead = '';
		$this->ridesByYear = $this->initRides;

		foreach ($rides as $ride)
			{
			if ($ride->unaffiliated)
				{
				continue;	// don't count unafilliated rides
				}

			if ($this->lastLeader != $ride->memberId)
				{
				$this->addRow();
				$this->lastLeader = $ride->memberId;
				}
			$date = $ride->rideDate;
			++$this->ridesByYear[(int)$date];

			if ($date > $today && ! $this->nextLead)
				{
				$this->nextLead = $date;
				}
			elseif ($date < $today)
				{
				$this->lastLead = $date;
				}
			}
		$this->addRow();

		if ($parameters['sort'] >= 0)
			{
			\usort($this->rows, '\App\Report\Leader::cmp');
			}

		if (isset($parameters['pdf']))
			{
			$this->outputPDF();
			}
		else
			{
			$this->outputCSV();
			}
		}

	public function generatePoints(array $parameters) : void
		{
		$prefix = 'VolunteerReport';
		$startDate = $parameters[$prefix . 'StartDateX'];
		$endDate = $parameters[$prefix . 'EndDateX'];
		$this->leaders = [];

		$categoryTable = new \App\Table\Category();
		$this->blankRow = [];

		foreach ($categoryTable->getAllCategories() as $cat)
			{
			$this->blankRow['Lead' . $cat->category] = 0;
			}
		$this->blankRow['LeadAll'] = 0;
		$this->blankRow['Assist'] = 0;
		$this->blankRow['Status'] = 0;
		$this->blankRow['CueSheet'] = 0;
		$this->blankRow['SignIn'] = [];
		$this->blankRow['Volunteer'] = 0;

		// volunteer
		if (! empty($parameters[$prefix . 'EventX']))
			{
			$volunteers = \App\Table\Member::getVolunteersForEvents($parameters[$prefix . 'EventX']);

			foreach ($volunteers as $volunteer)
				{
				if ($id = $this->addLeader((int)$volunteer['memberId']))
					{
					++$this->leaders[$id]['Volunteer'];
					}
				}
			}

		// cue sheets
		$cueSheets = \App\Table\CueSheet::getForDateRange($startDate, $endDate);

		foreach ($cueSheets as $cueSheet)
			{
			if ($id = $this->addLeader($cueSheet['memberId']))
				{
				++$this->leaders[$id]['CueSheet'];
				}
			}

		// signin sheets or online attendance
		$signinSheets = \App\Table\SigninSheet::getForDateRange($startDate, $endDate);

		foreach ($signinSheets as $signinSheet)
			{
			if ($id = $this->addLeader($signinSheet->memberId))
				{
				$this->leaders[$id]['SignIn'][$signinSheet->rideId] = 1;
				}
			}

		// ride status reported
		$rideStatusId = \App\Table\Ride::getRideStatus($startDate, $endDate);

		foreach ($rideStatusId as $status)
			{
			if ($id = $this->addLeader((int)$status['memberId']))
				{
				++$this->leaders[$id]['Status'];
				}
			}

		// assistant leads
		$assistantLeaders = \App\Table\AssistantLeader::getForDateRange($startDate, $endDate);

		foreach ($assistantLeaders as $leader)
			{
			if ($id = $this->addLeader((int)$leader['memberId']))
				{
				++$this->leaders[$id]['Assist'];
				}
			}

		$categoryTable = new \App\Table\Category();
		$paceTable = new \App\Table\Pace();
		// rides lead by category
		$rides = \App\Table\Ride::getDateRange(\App\Tools\Date::fromString($startDate), \App\Tools\Date::fromString($endDate));

		foreach ($rides as $ride)
			{
			if ($id = $this->addLeader($ride->memberId))
				{
				$cat = $categoryTable->getCategoryForId($paceTable->getCategoryIdFromPaceId($ride->paceId));
				++$this->leaders[$id]['Lead' . $cat];
				}
			}

		$this->addHeader('firstName', 'First', 'L', 25);
		$this->addHeader('lastName', 'Last', 'L', 25);
		$this->addHeader('email', 'email', 'L', 55);
		$baseRow = [];

		foreach ($parameters as $key => $value)
			{
			if (\str_contains($key, $prefix))
				{
				if (! empty($value) && ! \strpos($key, 'X'))
					{
					$key = \str_replace($prefix, '', $key);
					$this->addHeader($key, $key, 'C', 19);
					$baseRow[$key] = $value;
					}
				}
			}
		$this->addHeader('total', 'Total', 'C', 15);

		foreach ($this->leaders as $id => $leader)
			{
			if (! $id)
				{
				continue;
				}
			$row = ['firstName' => $leader['firstName'], 'lastName' => $leader['lastName'], 'email' => $leader['email']];
			$total = 0.0;

			foreach ($baseRow as $key => $value)
				{
				$count = \is_array($leader[$key]) ? \count($leader[$key]) : $leader[$key];
				$value = (int)$count * (float)$value;
				$total += $value;
				$row[$key] = $value;
				}
			$row['total'] = $total;
			$this->rows[] = $row;
			}
		self::$sort = 'lastName';

		\usort($this->rows, '\App\Report\Leader::cmp');

		if ('PDF' == $parameters['downloadType'])
			{
			$this->outputPDF();
			}
		else
			{
			$this->outputCSV();
			}
		}

	public function outputCSV() : void
		{
		$csv = new \App\Tools\CSV\FileWriter(\str_replace(' ', '', $this->reportName) . '.csv');
		$csv->outputRow($this->headerTitles);

		foreach ($this->rows as $row)
			{
			$data = [];

			foreach ($this->headerTitles as $index => $headerName)
				{
				$data[] = $row[$index];
				}
			$csv->outputRow($data);
			}
		}

	public function outputPDF() : void
		{
		$pdf = new \PDF_MC_Table('L', 'mm', 'Letter');
		$pdf->SetDisplayMode('fullpage');
		$pdf->SetFont('Arial', '', 10);
		$pdf->setNoLines(true);
		$pdf->headerFontSize = 18;
		$pdf->SetAutoPageBreak(true, 2);
		$pdf->SetDocumentTitle($this->reportName . ' - Printed ' . \App\Tools\Date::todayString());
		$pdf->SetWidths($this->columnWidths);
		$pdf->SetHeader($this->headerTitles);
		$pdf->SetAligns($this->columnAlign);
		$pdf->AddPage('L', 'Letter');
		$pdf->PrintHeader();

		foreach ($this->rows as $row)
			{
			$pdf->Row($row);
			}
		$pdf->Output(\str_replace(' ', '', $this->reportName) . '.pdf', 'I');
		}

	private function addHeader(string $index, string $header, string $align, int $size) : void
		{
		$this->headerTitles[$index] = $header;
		$this->columnWidths[$index] = $size;
		$this->columnAlign[$index] = $align;
		}

	private function addLeader(int $memberId) : int
		{
		if (! isset($this->leaders[$memberId]))
			{
			$member = new \App\Record\Member($memberId);

			if (! $member->loaded())
				{
				return 0;
				}
			$memberFields = ['firstName' => $member->firstName, 'lastName' => $member->lastName, 'email' => $member->email];
			$this->leaders[$memberId] = \array_merge($memberFields, $this->blankRow);
			}

		return $memberId;
		}

	private function addRow() : void
		{
		if ($this->lastLeader)
			{
			$leader = new \App\Record\Member($this->lastLeader);

			if ($leader->loaded())
				{
				$row = $this->ridesByYear;
				$row['last5'] = \array_sum($this->ridesByYear);
				$row['lastRide'] = $this->lastLead;
				$row['nextRide'] = $this->nextLead;
				$row['cat'] = \App\Table\MemberCategory::getRideCategoryStringForMember($this->lastLeader);
				$row['leader'] = \App\Tools\TextHelper::unhtmlentities($leader->fullName());
				$row['phone'] = $leader->phone;
				$row['email'] = $leader->email;
				$this->rows[] = $row;
				$this->nextLead = '';
				$this->lastLead = '';
				$this->ridesByYear = $this->initRides;
				}
			}
		}
	}
