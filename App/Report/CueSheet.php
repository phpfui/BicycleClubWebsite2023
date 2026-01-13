<?php

namespace App\Report;

class CueSheet extends \FPDF
	{
	private int $angle = 0;

	private float $ascent = 0;

	private float $distance = 0.0;

	private float $firstColumn;

	private string $font;

	private int $fontSize;

	private float $height = 0.0;

	private float $lastDistance = 0.0;

	private float $margin = 0.0;

	private float $secondColumn;

	private float $secondRow;

	private float $topMargin;

	private float $topRow;

	private string $units;

	private float $width;

	public function __construct()
		{
		parent::__construct('P', 'mm', 'letter');

		$settingTable = new \App\Table\Setting();
		$this->fontSize = (int)$settingTable->value('CueSheetFontSize');
		$this->font = $settingTable->value('CueSheetFont');
		$this->AddFont($this->font, '', $this->font . '.php');
		$this->AddFont($this->font, 'B', $this->font . '.php');

		$this->units = $settingTable->value('RWGPSUnits');
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

	public function cleanStreet(string $street, bool $minimize = true) : string
		{
		// do as much cleaning as we can
		$street = \PHPFUI\TextHelper::unicode_decode(\PHPFUI\TextHelper::unhtmlentities($street));
		$street = \preg_replace('/[^ -~]/', '', $street);
		$street = \str_replace('?', '', $street);

		if (! $minimize)
			{
			return $street;
			}

		$street = \str_replace('State Highway', 'RT', $street);
		$parts = \explode(' ', $street);

		if ('Turn' == $parts[0])
			{
			\array_shift($parts);
			}

		foreach ($parts as &$part)
			{
			$part = \str_replace(['Avenue', 'Drive', 'Street', 'Lane', 'Road', 'Place', 'left', 'right', 'onto', 'Route', ], ['Ave', 'Dr', 'St', 'Ln', 'Rd', 'Pl', 'L', 'R', '-', 'Rt', ], $part);
			}
		$parts[0] = \ucfirst($parts[0]);
		$street = \implode(' ', $parts);

		$parts = \explode(', ', $street);

		if (2 == \count($parts))
			{
			if ((int)$parts[1] == $parts[1])
				{
				if (! \str_contains($parts[0], 'Rt ' . $parts[1]))
					{
					$parts[1] = 'Rt ' . $parts[1];
					}
				else
					{
					unset($parts[1]);
					}
				}
			$street = \implode(', ', $parts);
			}

		return $street;
		}

	public function generate(\App\Tools\CSV\Reader $reader, string $title, string $leader = '', string $cellPhoneNumber = '') : static
		{
		$title = $this->cleanStreet($title, false);

		$topHeight = $this->height / 2. - $this->margin * 2.5;
		$bottomHeight = $this->height / 2. - $this->margin * 2;

		while ($reader->valid())
			{
			$this->newPage($title, $leader, $cellPhoneNumber);
			$this->printSection($this->firstColumn, $this->topRow, $topHeight, $reader);
			$this->printSection($this->secondColumn, $this->topRow, $topHeight, $reader);
			$this->printSection($this->firstColumn, $this->secondRow, $bottomHeight, $reader);
			$this->printSection($this->secondColumn, $this->secondRow, $bottomHeight, $reader);
			}

		return $this;
		}

	public function generateFromRide(\App\Record\Ride $ride, int $routeNumber = 0) : static
		{
		if (! $ride->loaded())
			{
			$this->newPage('Ride not found');

			return $this;
			}

		$leader = '';
		$cellPhoneNumber = '';
		$member = $ride->member;

		if ($member->loaded())
			{
			$leader = $member->fullName();
			$cellPhoneNumber = $member->cellPhone ?? '';
			}

		$count = 0;
		$RWGPS = new \App\Record\RWGPS();

		foreach ($ride->RWGPSChildren as $RWGPS)
			{
			if ($count++ >= $routeNumber)
				{
				break;
				}
			}

		return $this->generateFromRWGPS($RWGPS, $ride->title, $leader, $cellPhoneNumber);
		}

	public function generateFromRWGPS(\App\Record\RWGPS $rwgps, ?string $title = '', string $leader = '', string $cellPhoneNumber = '') : static
		{
		if (! $rwgps->loaded())
			{
			$this->newPage('RideWithGPS ride not found');

			return $this;
			}

		if (! \strlen($rwgps->csv ?? ''))
			{
			$this->newPage('Route has no turn information. Probably a recording.');

			return $this;
			}

		$title = $title ?: $rwgps->title;
		$this->ascent = $rwgps->elevationMeters;

		$this->distance = $rwgps->km * 1000;

		return $this->generate($rwgps->getCSVReader(), $title, $leader, $cellPhoneNumber);
		}

	private function getBigUnits(float $meters) : float
		{
		if ('Miles' == $this->units)
			{
			// return miles
			return $meters * 0.000621371;
			}

		// must want kilometers
		return $meters / 1000;
		}

	private function getSmallUnits(float $meters) : float
		{
		if ('Miles' == $this->units)
			{
			// return feet
			return $meters * 3.28084;
			}

		// must want meters
		return $meters;
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

	private function newPage(string $title, string $leader = '', string $cellPhoneNumber = '') : void
		{
		$this->AddPage();
		$this->SetFont($this->font, 'B', $this->fontSize);
		$y = $this->margin;


		$this->Text($this->margin, $this->topMargin, $title);
		$this->SetXY($this->width - 51, $y);
		$this->writeLabel('Dist', \number_format($this->getBigUnits($this->distance), 2) . ' ' . \substr($this->units, 0, 2));
		$ascent = \number_format($this->getSmallUnits($this->ascent), 2);
		$unit = 'Miles' == $this->units ? 'ft' : 'm';
		$this->writeLabel(' Ele', " +{$ascent} {$unit}");

		$this->setLineWidth(0.1);
		$this->SetDash(2, 2);
		// middle vertical
		$this->Line($this->width / 2.0, $this->margin * 2, $this->width / 2.0, $this->height - $this->margin);
		// middle horizontal
		$this->Line($this->margin, $this->height / 2.0, $this->width - $this->margin, $this->height / 2.0);
		$this->SetDash();

		$end = $this->height / 2 - $this->margin * 2.5;
		$this->printLeader($this->firstColumn, $this->topRow + $end, $leader, $cellPhoneNumber);
		$this->printLeader($this->secondColumn, $this->topRow + $end, $leader, $cellPhoneNumber);
		$this->printLeader($this->firstColumn, $this->secondRow + $end, $leader, $cellPhoneNumber);
		$this->printLeader($this->secondColumn, $this->secondRow + $end, $leader, $cellPhoneNumber);
		}

	private function printLeader(float $column, float $row, string $leader, string $cellPhoneNumber) : void
		{
		$this->SetXY($column, $row);
		$this->writeLabel('Leader', $leader);
		$this->SetXY($column + 65, $row);
		$this->writeLabel('Cell', $cellPhoneNumber);
		}

	/** @param array<string, string> $row */
	private function printRow(float $x, float $y, array $row, string $border, float $lineHeight = 7.0, string $alignment = 'R') : float
		{
		$height = $lineHeight;
		$this->setXY($x, $y);

		$row['street'] = $row['street'] ?? '';
		$street = $this->cleanStreet($row['street']);
		$streetWidth = 65;
		$currentBorder = $border;
		$streetContinued = $this->limit($street, $streetWidth);

		if ($streetContinued)
			{
			$currentBorder = \str_replace('B', '', $border);
			}

		$this->setLineWidth(0.2);
		$this->Cell(14, $lineHeight, $row['distance'], $currentBorder, 0, $alignment, true);
		$this->Cell(12, $lineHeight, $row['gox'], $currentBorder, 0, $alignment, true);
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


				case 'Left_sharp':
				case 'Left_slight':
				case 'Left':

					$turnX += 4.75;
					$turnY += 1.5;
					$angle = 180;

					break;


				case 'Flag, Blue':
				case 'Start':

					$turnY += $lineHeight - 1.5;
					$turnX += 0.5;
					$char = \chr(72);

					break;


				case 'Right_sharp':
				case 'Right_slight':
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
			$this->SetFont($this->font, '', $this->fontSize);
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

	private function printSection(float $x, float $y, float $height, \App\Tools\CSV\Reader $reader) : void
		{
		$this->SetXY($x, $y);

		if (! $reader->valid())
			{
			return;
			}

		$maxY = $y + $height - 14;

		$this->SetFont($this->font, 'B', 8);
		$this->SetFillColor(204);

		$header = [];
		$header['distance'] = 'Distance';
		$header['turn'] = '';
		$header['gox'] = 'Go X';
		$header['street'] = '';
		$y += $this->printRow($x, $y, $header, 'TL', 4, 'C');
		$this->SetFont($this->font, 'B', 8);
		$header['distance'] = 'At Turn';
		$header['gox'] = $this->units;
		$header['street'] = 'Then Turn Onto';
		$y += $this->printRow($x, $y, $header, 'LB', 4, 'C');

		$this->SetFont($this->font, '', $this->fontSize);
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
			$originalDistance = (float)$row['distance'];
			$row['distance'] = \number_format($this->getBigUnits($originalDistance), 2);
			$row['gox'] = \number_format($this->getBigUnits($originalDistance - $this->lastDistance), 2);
			$this->lastDistance = $originalDistance;
			$y += $this->printRow($x, $y, $row, 'LB');
			$reader->next();
			++$count;
			}
		}

	private function Rotate(int $angle, float $x = -1.0, float $y = -1.0) : void
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
		if (\strlen($value))
			{
			$this->SetFont($this->font, 'B', 8);
			$this->Write(2.7, $label . ': ');
			$this->SetFont($this->font, '');
			$this->Write(2.7, $value);
			}
		}
	}
