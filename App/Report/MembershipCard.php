<?php

namespace App\Report;

class MembershipCard
	{
	protected string $abbrev;

	protected string $address;

	protected string $clubName;

	protected \FPDF $pdf;

	protected bool $printed = false;

	protected string $town;

	protected string $www;

	private readonly \App\Table\Setting $settingTable;

	public function __construct()
		{
		$this->settingTable = new \App\Table\Setting();
		$this->abbrev = $this->settingTable->value('clubAbbrev');
		$this->clubName = $this->settingTable->value('clubName');
		$this->address = $this->settingTable->value('memberAddr');
		$this->www = $this->settingTable->value('homePage');
		$this->town = $this->settingTable->value('memberTown');
		$this->pdf = new \FPDF('P', 'in', 'Letter');
		$this->pdf->SetTitle($this->abbrev . ' Membership Card');
		}

	public function __destruct()
		{
		if ($this->printed)
			{
			$this->pdf->Output($this->abbrev . '-MembershipCard.pdf', 'I');
			}
		}

	public function generate(\App\Record\Member $member) : void
		{
		$this->printed = true;
		$this->pdf->AddPage();
		$this->pdf->SetMargins(1, 1);
		$firstName = $member->firstName;
		$lastName = $member->lastName;
		$memberId = $member->memberId;
		$membershipId = $member->membershipId;
		$this->pdf->SetFont('Arial', 'B', 16);
		$instructions = $this->clubName . ' Membership Card Instructions';
		$this->pdf->Cell(0, 1, $instructions, 0, 1, 'C');
		$this->pdf->SetFont('Arial', '', 12);
		$instructions = 'You should have a color printer.  Print page one of this document.  Remove the printed page from your printer. ' . 'Reinsert it back into the paper supply by flipping it over side to side.  Do not flip it upside down.  Now print the ' . 'second page on the back of the first page.  Cut the double sided card out of the middle of the paper.  Use the guides as ' . 'a reference when you cut it.  The membership card should be a standard business card size of 3.5 by 2 inches.  You should sign the' . ' back when you are done.  You can laminate the card if you want.';
		$this->pdf->MultiCell(0, .180, $instructions);
		$this->pdf->SetXY(2, 4);
		$this->pdf->Cell(.5, .5, '', 'RB');
		$this->pdf->SetXY(6, 4);
		$this->pdf->Cell(.5, .5, '', 'LB');
		$this->pdf->SetXY(2.5, 4.5);
		$line = 5.6;
		$this->pdf->SetFont('Arial', 'B', 12);
		$infoline = $line + 0.1;
		$this->pdf->SetXY(2.6, $infoline);
		$this->pdf->Cell(3.5, .2, 'Membership Card');
		$infoline += 0.2;
		$this->pdf->SetXY(2.6, $infoline);
		$memberInfo = \App\Tools\TextHelper::unhtmlentities($firstName . ' ' . $lastName);
		$this->pdf->SetFont('Arial', '', 10);
		$this->pdf->Cell(2.6, .2, $memberInfo);
		$infoline += 0.2;
		$this->pdf->SetXY(2.6, $infoline);
		$this->pdf->Cell(2.6, .2, 'Valid Through: ' . \App\Tools\Date::formatString('m/y', $member->membership->expires));
		$this->pdf->SetFont('Arial', '', 8);
		$this->pdf->SetXY(4.25, $line);
		$this->pdf->Cell(1.75, .2, $this->clubName);
		$line += .1;
		$this->pdf->SetXY(4.25, $line);
		$this->pdf->Cell(1.75, .2, $this->address);
		$line += .1;
		$this->pdf->SetXY(4.25, $line);
		$this->pdf->Cell(1.75, .2, $this->town);
		$line += .15;
		$this->pdf->SetXY(4.25, $line);
		$this->pdf->Cell(1.75, .2, $this->www);
		$line += .15;
		$this->pdf->SetXY(4.25, $line);
		$this->pdf->Cell(1.75, .2, 'Cycling, Skiing');
		$line += .12;
		$this->pdf->SetXY(4.25, $line);
		$this->pdf->Cell(1.75, .2, 'Education, Advocacy');
		$file = new \App\Model\ImageFiles();
		$this->pdf->Image($file->get($this->settingTable->value('cardLogo')), 2.9, 4.55, 2.7);
		$this->pdf->SetXY(2, 6.5);
		$this->pdf->Cell(.5, .5, '', 'TR');
		$this->pdf->SetXY(6, 6.5);
		$this->pdf->Cell(.5, .5, '', 'LT');
		$this->pdf->AddPage();
		$this->pdf->SetMargins(1, 1);
		$this->pdf->SetXY(2, 4);
		$this->pdf->Cell(.5, .5, '', 'RB');
		$this->pdf->SetXY(6, 4);
		$this->pdf->Cell(.5, .5, '', 'LB');
		$this->pdf->SetXY(2.5, 4.5);
		$this->pdf->SetFont('Arial', '', 10);
		$this->pdf->SetXY(2, 6.5);
		$this->pdf->Cell(.5, .5, '', 'TR');
		$this->pdf->SetXY(6, 6.5);
		$this->pdf->Cell(.5, .5, '', 'LT');
		$this->pdf->Text(2.6, 6.2, 'Signature: _________________________________');
		$this->pdf->Text(3.3, 6.4, $memberInfo);
		$this->pdf->Text(2.6, 4.7, "Membership Number: {$membershipId} Member Number: {$memberId}");
		}
	}
