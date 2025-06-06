<?php

namespace App\Report;

class RideWaiver extends \Mpdf\Mpdf
	{
	/**
	 * @var array<int>
	 */
	protected array $columns = [10, 51, 51, 30, 30, 19, ];

	protected int $length = 0;

	protected \PHPFUI\ORM\DataObjectCursor $riders;

	/**
	 * @var array<int>
	 */
	protected array $startOffset = [];

	private readonly \App\Table\Setting $settingTable;

	public function __construct()
		{
		$this->settingTable = new \App\Table\Setting();
		$config = ['format' => 'LETTER'];
		$config['mode'] = 'utf-8';
		parent::__construct($config);
		$this->SetAutoPageBreak(false);

		foreach ($this->columns as $key => $value)
			{
			$this->length += $value;
			$this->startOffset[$key] = $this->length + 10;  // 10 is left margin
			}
		}

	public function generateRiders(\App\Record\Ride $ride) : void
		{
		$memberTable = new \App\Table\Member();
		$memberTable->addJoin('rideSignup');
		$memberTable->addOrderBy('firstName');
		$memberTable->addOrderBy('lastName');
		$memberTable->setWhere(new \PHPFUI\ORM\Condition('rideId', $ride->rideId));
		$this->AddPage();
		$this->SetMargins(12, 12, 5);

		$status = \App\Table\RideSignup::getRiderStatus();

		$table = new \PHPFUI\Table();
		$table->setHeaders(['firstName' => 'First Name', 'lastName' => 'Last Name', 'status' => 'Signuped As', 'cellPhone' => 'Rider Cell', 'emergencyContact' => 'Contact', 'emergencyPhone' => 'Contact Phone']);

		foreach ($memberTable->getArrayCursor() as $rider)
			{
			$rider['status'] = $status[$rider['status']];
			$table->addRow($rider);
			}
		$this->WriteHTML(new \PHPFUI\Header("Signed Up Riders for {$ride->title} on {$ride->rideDate}", 4));
		$this->WriteHTML("{$table}");
		}

	public function generateRideSignupWaiver(\App\Record\Member $member, \App\Record\Ride $ride) : void
		{
		$rideSignup = new \App\Record\RideSignup(['rideId' => $ride->rideId, 'memberId' => $member->memberId]);

		if (! $rideSignup->loaded())
			{
			$this->writeHTML('Member was not signed up on this ride');
			}
		else
			{
			$this->writeHTML($this->settingTable->value('WaiverText'));

			$container = new \PHPFUI\Container();
			$container->add(new \PHPFUI\SubHeader('The above was signed as follows:'));
			$table = new \PHPFUI\Table();
			$table->addRow($this->format('Name:', $member->firstName . ' ' . $member->lastName));
			$table->addRow($this->format('Phone:', $member->phone));
			$table->addRow($this->format('Cell Phone:', $member->cellPhone));
			$table->addRow($this->format('email:', $member->email));
			$table->addRow($this->format('Signed At:', \date('l jS \of F Y h:i:s A', \strtotime($rideSignup->signedUpTime))));
			$table->addRow($this->format('Printed At:', \date('l jS \of F Y h:i:s A')));
			$container->add($table);
			$this->writeHTML($container);
			}
		}

	public function generateSignupSheetWaiver(\App\Record\Ride $ride) : void
		{
		$ride->releasePrinted = \date('Y-m-d H:i:s');
		$ride->update();
		$rideSignupTable = new \App\Table\RideSignup();
		$this->riders = $rideSignupTable->getSignedUpRiders($ride->rideId, 'm.lastName,m.firstName');
		$this->AddPage();
		$this->SetMargins(12, 12, 5);
		$this->printHeader($ride);
		$this->setXY(10, 24);
		$this->WriteHtml($this->settingTable->value('WaiverText'));

		$i = 1;
		$sigsOnPage = 29;

		while (\count($this->riders) >= $i)
			{
			$this->AddPage();
			$this->setXY(10, 10);
			$this->printHeader($ride);
			$this->setXY(10, 20);
			$this->printSignatureHeader();

			for ($j = $i; $j < $i + $sigsOnPage; ++$j)
				{
				$this->printSignatureLine($j, $this->riders->current()->toArray());
				$this->riders->next();
				}
			$i += $sigsOnPage;
			$this->printAddressLine();
			}
		}

	/**
	 * @return array<string>
	 */
	protected function format(string $label, string $value) : array
		{
		return ["<strong>{$label}</strong>", $value];
		}

	protected function printAddressLine() : void
		{
		$this->SetFont('Arial', '', 8);
		$this->SetY($this->y + 3);
		$this->Cell(0, 0, 'Please scan or photograph this completed form, attach it to an email and send to: signinsheets@' . \emailServerName(), 0, 0, 'C');
		}

	protected function printHeader(\App\Record\Ride $ride) : void
		{
		$date = \App\Tools\Date::formatString('F j, Y', $ride->rideDate);
		$title = \App\Tools\TextHelper::unhtmlentities($ride->title);
		$paceTable = new \App\Table\Pace();
		$memberInfo = $ride->member;
		$leader = $memberInfo->loaded() ? \App\Tools\TextHelper::unhtmlentities($memberInfo->fullName()) : 'Leaderless';
		$this->SetTitle("Ride Sign Up Sheet for {$date} {$title}");
		$this->SetFont('Times', '', 8);
		$y = $this->y;
		$x = 10;
		$this->WriteText($x, $y, 'RideId: ');
		$x += 10;
		$this->SetFont('Times', 'B', 8);
		$this->WriteText($x, $y, "{$ride->rideId}");
		$x += 12;
		$this->SetFont('Times', '', 8);
		$this->WriteText($x, $y, 'Date: ');
		$x += 8;
		$this->SetFont('Times', 'B', 8);
		$this->WriteText($x, $y, $date);
		$x += 40;
		$this->SetFont('Times', '', 8);
		$this->WriteText($x, $y, 'Leader: ');
		$x += 11;
		$this->SetFont('Times', 'B', 8);
		$this->WriteText($x, $y, $leader);
		$x += 100;
		$this->SetFont('Times', '', 8);
		$this->WriteText($x, $y, 'Cat: ');
		$x += 7;
		$this->SetFont('Times', 'B', 8);
		$this->WriteText($x, $y, $paceTable->getPace($ride->paceId));
		$y += 4;
		$x = 10;
		$this->SetFont('Times', '', 8);
		$this->WriteText($x, $y, 'Title: ');
		$x += 8;
		$this->SetFont('Times', 'B', 8);
		$this->WriteText($x, $y, $title);
		}

	protected function printSignatureHeader() : void
		{
		$height = 12;
		$y = $this->y;
		$lx = $this->x;
		$rx = $this->x;
		$this->Line($lx, $y, $lx + $this->length, $y);

		foreach ($this->columns as $value)
			{
			$columns[] = $value - 3;
			$this->Line($lx, $y, $lx, $y + $height);
			$lx += $value;
			}
		$this->Line($lx, $y, $lx, $y + $height);
		$this->SetY($y + $height);
		$this->Line($rx, $this->y, $rx + $this->length, $this->y);
		$this->SetFont('Times', 'B', 10);
		$this->SetXY($this->startOffset[0], $y);
		$this->Cell($this->columns[1], $height / 2, "Participant's", 0, 2, 'C');
		$this->Cell($this->columns[1], $height / 4, 'Signature', 0, 0, 'C');
		$this->SetXY($this->startOffset[1], $y);
		$this->Cell($this->columns[2], $height, 'Printed Name', 0, 2, 'C');
		$this->SetXY($this->startOffset[2], $y);
		$this->Cell($this->columns[3], $height / 2, 'Emergency', 0, 2, 'C');
		$this->Cell($this->columns[3], $height / 4, 'Contact No.', 0, 2, 'C');
		$this->SetXY($this->startOffset[3], $y);
		$this->Cell($this->columns[4], $height / 2, 'Your Cell', 0, 2, 'C');
		$this->Cell($this->columns[4], $height / 4, 'Phone Number', 0, 2, 'C');
		$this->SetXY($this->startOffset[4], $y);
		$this->Cell($this->columns[5], $height / 2, 'Plate', 0, 2, 'C');
		$this->Cell($this->columns[5], $height / 4, 'Number', 0, 2, 'C');
		$this->SetY($y + $height);
		$this->SetX($rx);
		}

	/**
	 * @param array<string,string> $member
	 */
	protected function printSignatureLine(int $i, array $member) : void
		{
		$height = 8;
		$y = $this->y;
		$lx = $this->x;
		$rx = $this->x;

		foreach ($this->columns as $value)
			{
			$this->Line($lx, $y, $lx, $y + $height);
			$lx += $value;
			}
		$this->Line($lx, $y, $lx, $y + $height);
		$this->SetFont('Arial', '', 8);

		if (isset($member['firstName']))
			{
			$this->SetXY($this->startOffset[1], $y);
			$this->Cell($this->columns[2], $height, \App\Tools\TextHelper::unhtmlentities($member['firstName'] . ' ' . $member['lastName']), 0, 2, 'l');
			$this->SetXY($this->startOffset[4], $y);
			$this->Cell($this->columns[5], $height, \App\Tools\TextHelper::unhtmlentities($member['license']), 0, 2, 'C');
			}

		if (isset($member['emergencyPhone']))
			{
			$this->SetXY($this->startOffset[2], $y);
			$this->Cell($this->columns[3], $height / 2, \App\Tools\TextHelper::unhtmlentities($member['emergencyPhone']), 0, 2, 'R');
			}

		if (isset($member['emergencyContact']))
			{
			$this->SetXY($this->startOffset[2], $y + $height / 2);
			$this->Cell($this->columns[3], $height / 2, \App\Tools\TextHelper::unhtmlentities($member['emergencyContact']), 0, 2, 'R');
			}

		if (isset($member['cellPhone']))
			{
			$this->SetXY($this->startOffset[3], $y);
			$this->Cell($this->columns[4], $height, \App\Tools\TextHelper::unhtmlentities($member['cellPhone']), 0, 2, 'R');
			}
		$this->SetFont('Times', 'B', 10);
		$this->SetXY(10, $y);
		$this->Cell($this->columns[0], $height, $i . '.', 0, 2, 'C');
		$this->SetXY($this->startOffset[0], $y);
		$grey = 150;
		$this->SetTextColor($grey, $grey, $grey);
		$this->Cell($this->columns[1], $height, 'I have read this release', 0, 2, 'C');
		$this->SetTextColor(0, 0, 0);
		$this->SetY($y + $height);
		$this->Line($rx, $this->y, $rx + $this->length, $this->y);
		$this->SetX($rx);
		}

	protected function signatureLine(string $text, int $length) : void
		{
		$x = $this->x;
		$y = $this->y;
		$this->Line($x, $y, $x + $length, $y);
		$this->SetFont('Times', '', 8);
		$this->SetXY($x, $y + 1);
		$this->Write(3, $text);
		$this->SetXY($x, $y);
		}
	}
