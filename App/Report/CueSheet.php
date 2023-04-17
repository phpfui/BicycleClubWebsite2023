<?php

namespace App\Report;

class CueSheet extends \FPDF
	{
	private int $angle = 0;

	private readonly float $firstColumn;

	private float $height = 0.0;

	private float $lastDistance = 0.0;

	private float $margin = 0.0;

	private \App\Record\RWGPS $rwgps;

	private readonly \App\Model\RideWithGPS $rwgpsModel;

	private readonly float $secondColumn;

	private readonly float $secondRow;

	private readonly float $topMargin;

	private readonly float $topRow;

	private readonly float $width;

	public function __construct()
		{
		parent::__construct('P', 'mm', 'letter');
		$this->rwgpsModel = new \App\Model\RideWithGPS();
		$this->SetAutoPageBreak(false);
		$this->width = $this->GetPageWidth();
		$this->height = $this->GetPageHeight();
		$this->topMargin = 25.4 / 3;
		$this->margin = 25.4 / 4;
		$this->firstColumn = $this->margin;
		$this->secondColumn = $this->margin + $this->width / 2.0;
		$this->topRow = $this->margin * 1.5;
		$this->secondRow = $this->height / 2.0 + $this->margin;
		$this->SetMargins($this->margin, $this->topMargin, $this->margin);
		}

	public function generateFromRide(\App\Record\Ride $ride) : static
		{
		if (! $ride->loaded())
			{
			$this->newPage('Ride not found');

			return $this;
			}

		$this->rwgps = $ride->RWGPS;

		if (! $this->rwgps->loaded())
			{
			$this->newPage('RideWithGPS ride not found');

			return $this;
			}

		$leader = '';
		$cell = '';

		$member = $ride->member;

		if ($member->loaded())
			{
			$leader = $member->fullName();
			$cell = $member->cellPhone;
			}

		return $this->generate($ride->title, $leader, $cell);
		}

	public function generateFromRWGPS(\App\Record\RWGPS $rwgps) : static
		{
		$this->rwgps = $rwgps;

		if (! $this->rwgps->loaded())
			{
			$this->newPage('RideWithGPS ride not found');

			return $this;
			}

		return $this->generate($this->rwgps->title);
		}

	private function generate(string $title, string $leader = '', string $cell = '') : static
		{
		$reader = $this->rwgpsModel->getCSVReader($this->rwgps->csv ?? '');

		$title = $this->rwgpsModel->cleanStreet($title, false);

		$topHeight = $this->height / 2.0 - $this->margin * 2.5;
		$bottomHeight = $this->height / 2.0 - $this->margin * 2;

		while ($reader->valid())
			{
			$this->newPage($title, $leader, $cell);
			$this->printSection($this->firstColumn, $this->topRow, $topHeight, $reader);
			$this->printSection($this->secondColumn, $this->topRow, $topHeight, $reader);
			$this->printSection($this->firstColumn, $this->secondRow, $bottomHeight, $reader);
			$this->printSection($this->secondColumn, $this->secondRow, $bottomHeight, $reader);
			}

		return $this;
		}

	private function limit(string &$street, float $streetWidth) : string
		{
		$printWidth = $this->GetStringWidth($street);

		$streetContinued = '';

		if ($streetWidth < $printWidth)
			{
			$parts = \explode(' ', $street);
			$street = '';
			$part = \array_shift($parts) . ' ';

			do
				{
				$street .= $part;
				$part = \array_shift($parts) . ' ';
				}
			while (\count($parts) && $streetWidth > $this->GetStringWidth($street . $part));
			\array_unshift($parts, \trim($part));
			$streetContinued = \implode(' ', $parts);
			}

		return $streetContinued;
		}

	private function newPage(string $title, string $leader = '', string $cell = '') : void
		{
		$this->AddPage();
		$this->SetFont('Arial', 'B', 14);
		$y = $this->margin;


		$this->Text($this->margin, $this->topMargin, $title);
		$this->SetXY($this->width - 50, $y);
		$this->writeLabel('Mileage', $this->rwgps->mileage ?? '');
		$this->writeLabel(' Elevation', $this->rwgps->elevation ?? '');

		$this->setLineWidth(0.1);
		$this->SetDash(2, 2);
		// middle vertical
		$this->Line($this->width / 2.0, $this->margin * 2, $this->width / 2.0, $this->height - $this->margin);
		// middle horizontal
		$this->Line($this->margin, $this->height / 2.0, $this->width - $this->margin, $this->height / 2.0);
		$this->SetDash();

		$end = $this->height / 2 - $this->margin * 2.5;
		$this->printLeader($this->firstColumn, $this->topRow + $end, $leader, $cell);
		$this->printLeader($this->secondColumn, $this->topRow + $end, $leader, $cell);
		$this->printLeader($this->firstColumn, $this->secondRow + $end, $leader, $cell);
		$this->printLeader($this->secondColumn, $this->secondRow + $end, $leader, $cell);
		}

	private function printLeader(float $column, float $row, string $leader, string $cell) : void
		{
		$this->SetXY($column, $row);
		$this->writeLabel('Leader', $leader);
		$this->SetXY($column + 65, $row);
		$this->writeLabel('Cell', $cell);
		}

	private function printRow(float $x, float $y, array $row, string $border, float $lineHeight = 7.0) : float
		{
		$height = $lineHeight;
		$this->setXY($x, $y);
		$row['street'] ??= '';
		$street = $this->rwgpsModel->cleanStreet($row['street']);
		$streetWidth = 65;
		$currentBorder = $border;
		$streetContinued = $this->limit($street, $streetWidth);

		if ($streetContinued)
			{
			$currentBorder = \str_replace('B', '', $border);
			}

		$this->setLineWidth(0.2);
		$this->Cell(14, $lineHeight, $row['distance'], $currentBorder, 0, 'C', true);
		$this->Cell(12, $lineHeight, $row['gox'], $currentBorder, 0, 'C', true);
		$turnY = $this->GetY();
		$turnX = $this->GetX();
		$this->Cell(5, $lineHeight, '', $currentBorder, 0, 'C', true);
		$currentBorder .= 'R';
		$this->Cell($streetWidth, $lineHeight, $street, $currentBorder, 0, 'L', true);

		if ($row['turn'] ?? '')
			{
			$this->SetXY($turnX, $turnY);
			$char = \chr(228);
			$angle = 0;

			switch ($row['turn'])
				{
				case 'Straight':

					$turnX += 4;
					$turnY += $lineHeight - 1;
					$angle = 90;

					break;


				case 'Left':

					$turnX += 4.75;
					$turnY += 1.5;
					$angle = 180;

					break;


				case 'Start':

					$turnY += $lineHeight - 1.5;
					$turnX += 0.5;
					$char = \chr(72);

					break;


				case 'Right':

					$turnY += $lineHeight - 2;
					$turnX += 0.25;

					break;


				case 'End':

					$char = \chr(54);
					$turnY += $lineHeight - 2;
					$turnX += 0.5;

					break;


				default:

					$char = '';

				}
			$this->Rotate($angle, $turnX, $turnY);
			$this->SetFont('ZapfDingbats', '', 14);
			$this->Text($turnX, $turnY, $char);
			$this->Rotate(0);
			$this->SetFont('Arial', '', 14);
			}

		while ($streetContinued)
			{
			$street = $streetContinued;
			$currentBorder = $border;
			$streetContinued = $this->limit($street, $streetWidth);

			if ($streetContinued)
				{
				$currentBorder = \str_replace('B', '', $border);
				}
			$this->setXY($x, $y + $height);
			$this->Cell(14, $lineHeight, '', $currentBorder, 0, 'C', true);
			$this->Cell(12, $lineHeight, '', $currentBorder, 0, 'C', true);
			$this->Cell(5, $lineHeight, '', $currentBorder, 0, 'C', true);
			$currentBorder .= 'R';
			$this->Cell($streetWidth, $lineHeight, $street, $currentBorder, 0, 'L', true);
			$height += $lineHeight;
			}

		return $height;
		}

	private function printSection(float $x, float $y, float $height, \App\Tools\CSVReader $reader) : void
		{
		$this->SetXY($x, $y);

		if (! $reader->valid())
			{
			return;
			}

		$maxY = $y + $height - 14;

		$this->SetFont('Arial', 'B', 8);
		$this->SetFillColor(204);

		$row = [];
		$row['distance'] = 'Distance';
		$row['turn'] = '';
		$row['gox'] = 'Go X';
		$row['street'] = '';
		$y += $this->printRow($x, $y, $row, 'TL', 4);
		$this->SetFont('Arial', 'B', 8);
		$row['distance'] = 'At Turn';
		$row['gox'] = 'Miles';
		$row['street'] = 'Then Turn Onto';
		$y += $this->printRow($x, $y, $row, 'LB', 4);

		$this->SetFont('Arial', '', 14);
		$count = 1;

		while ($y < $maxY && $reader->valid())
			{
			if ($count % 2)
				{
				$this->SetFillColor(255);
				}
			else
				{
				$this->SetFillColor(238);
				}

			$row = $reader->current();
			$row['distance'] ??= 0;
			$row['distance'] = \number_format($row['distance'], 2);
			$row['gox'] = \number_format((float)$row['distance'] - (float)$this->lastDistance, 2);
			$this->lastDistance = (float)$row['distance'];
			$y += $this->printRow($x, $y, $row, 'LB');
			$reader->next();
			++$count;
			}
		}

	private function Rotate(int $angle, $x = -1, $y = -1) : void
		{
		if (-1 == $x)
			{
			$x = $this->x;
			}

		if (-1 == $y)
			{
			$y = $this->y;
			}

		if (0 != $this->angle)
			{
			$this->_out('Q');
			}
		$this->angle = $angle;

		if (0 != $angle)
			{
			$angle *= M_PI / 180;
			$c = \cos($angle);
			$s = \sin($angle);
			$cx = $x * $this->k;
			$cy = ($this->h - $y) * $this->k;
			$this->_out(\sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
			}
		}

	private function SetDash(?float $black = null, ?float $white = null) : static
		{
		if (null !== $black)
			{
			$s = \sprintf('[%.3F %.3F] 0 d', $black * $this->k, $white * $this->k);
			}
		else
			{
			$s = '[] 0 d';
			}
		$this->_out($s);

		return $this;
		}

	private function writeLabel(string $label, string $value) : void
		{
		if ($value)
			{
			$this->SetFont('Arial', 'B', 8);
			$this->Write(2.7, $label . ': ');
			$this->SetFont('Arial', '');
			$this->Write(2.7, $value);
			}
		}
	}
