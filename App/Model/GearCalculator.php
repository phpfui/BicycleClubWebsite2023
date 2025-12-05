<?php

namespace App\Model;

/**
 * @property string $c Cassette
 * @property string $fh Front Hub
 * @property string $rh Read Hub
 * @property int $p Precision
 * @property string $t Tire Size
 * @property string $tl Title
 * @property string $u Update
 * @property string $uf Update Front Hub
 * @property string $ur Update Rear Hub
 * @property string $uc Update Cassette
 */
class GearCalculator
	{
	use \App\Tools\SchemeHost;

	/** @var array<int> */
	private array $cogs = [];

	/** @var array<float> */
	private array $frontHub = [];

	/** @var array<float> */
	private array $rearHub = [];

	/** @var array<int> */
	private array $rings = [];

	/**
	 * @param array<string,string|int> $parameters
	 */
	public function __construct(private array $parameters)
		{
		unset($this->parameters['csrf']);

		if (empty($this->ufh))
			{
			$this->frontHub = $this->getFloatArray('fh');
			}
		else
			{
			$this->frontHub = $this->getHubArray('fh');
			}

		if (empty($this->urh))
			{
			$this->rearHub = $this->getFloatArray('rh');
			}
		else
			{
			$this->rearHub = $this->getHubArray('rh');
			}

		$this->rings = $this->getIntArray('ring');

		if (empty($this->rings))
			{
			$this->rings = [46, 33];
			}

		if (empty($this->uc) || empty($this->c))
			{
			$this->cogs = $this->getIntArray('cog');
			}

		if (empty($this->cogs))
			{
			foreach (\explode('-', $this->c ?? '') as $cog)
				{
				if ($cog)
					{
					$this->cogs[] = (int)$cog;
					}
				}
			}

		if (empty($this->cogs))
			{
			$this->cogs = [10, 11, 12, 13, 14, 15, 17, 19, 21, 24, 28, 33, ];
			}

		$this->parameters['p'] = \min(\max((int)($this->parameters['p'] ?? 2), 0), 5);
		}

	public function __get(string $field) : ?string
		{
		if (\array_key_exists($field, $this->parameters))
			{
			return $this->parameters[$field];
			}

		return null;
		}

	/**
	 * Allows for empty($object->field) to work correctly
	 */
	public function __isset(string $field) : bool
		{
		return \array_key_exists($field, $this->parameters);
		}

	public function computeGear(float $ring, int $cog) : string
		{
		if (empty($cog))
			{
			return '';
			}
		$parts = \explode('~', $this->t ?? '622~28');
		$diameter = $parts[0];
		$unit = $this->u ?? '0';

		switch ($unit)
			{
			case '0': // gear inches
				// the diameter of the drive wheel, times the size of the front sprocket divided by the size of the rear sprocket
				$gear = (int)$diameter * 0.0393700787 * $ring / $cog;

				return \number_format($gear, $this->p);

			case '1': // gear ratio
				$gear = $ring / $cog;

				return \number_format($gear, $this->p);

			case '2': // meters development
				$gear = (int)$diameter * M_PI * $ring / $cog / 1000 ;

				return \number_format($gear, $this->p);
			}

		// compute speed
		if (str_contains($unit, '~'))
			{
			[$rpm, $units] = \explode('~', $unit);
			}
		else
			{
			$rpm = $unit ? : 80;
			$units = 'M';
			}
		$gear = (int)$diameter * M_PI * $ring / $cog * (int)$rpm * 60 / 1000000;

		if ('M' == $units)
			{
			$gear *= 0.621371;
			}

		return \number_format($gear, $this->p) . " {$units}PH";
		}

	public function csv() : void
		{
		$writer = new \App\Tools\CSV\FileWriter('GearCalculator.csv');
		$writer->addHeaderRow(false);

		foreach ($this->getTable()->getRows() as $row)
			{
			foreach ($row as &$cell)
				{
				$cell = \strip_tags($cell);
				}
			$writer->outputRow($row);
			}
		}

	public function getCassette() : string
		{
		return \implode('-', $this->cogs);
		}

	/** @return array<int> */
	public function getCogs() : array
		{
		return $this->cogs;
		}

	/**
	 * Gear * 2 across (highest first) / cogs down
	 *
	 * @param array<float> $hub1
	 * @param array<float> $hub2
	 */
	public function getDualHubCassetteTable(int $ring, array $hub1, array $hub2) : \PHPFUI\Table
		{
		$this->orderHubs($hub1, $hub2);

		$table = new \PHPFUI\Table();
		$headers = ['<b>Gear</b>'];

		$gear1 = \count($hub1);

		foreach ($hub1 as $ratio1)
			{
			$gear2 = \count($hub2);

			foreach ($hub2 as $ratio2)
				{
				$headers[] = "<b>{$gear1}-{$gear2}</b>";
				--$gear2;
				}
			--$gear1;
			}

		$table->addRow($headers);

		foreach ($this->cogs as $cog)
			{
			$row = ["<b>{$cog}</b>"];

			foreach ($hub1 as $ratio1)
				{
				$gear2 = \count($hub2);

				foreach ($hub2 as $ratio2)
					{
					$row[] = $this->computeGear($ring * $ratio1 * $ratio2, $cog);
					}
				}
			$table->addRow($row);
			}

		return $table;
		}

	/**
	 * More traditional 2 x gear chart but with hubs
	 *
	 * @param array<float> $hub1
	 * @param array<float> $hub2
	 */
	public function getDualHubTable(int $ring, int $cog, array $hub1, array $hub2) : \PHPFUI\Table
		{
		$this->orderHubs($hub1, $hub2);

		$table = new \PHPFUI\Table();

		$headers = ['<b>Gears</b>'];

		$gear = \count($hub1);

		foreach ($hub1 as $ratio)
			{
			$headers[] = "<b>{$gear}</b>";
			--$gear;
			}

		$table->addRow($headers);

		$gear = \count($hub2);

		foreach ($hub2 as $cogRatio)
			{
			$row = ["<b>{$gear}</b>"];

			foreach ($hub1 as $ringRatio)
				{
				$row[] = $this->computeGear((float)$ring * $cogRatio * $ringRatio, $cog);
				}
			$table->addRow($row);
			--$gear;
			}

		return $table;
		}

	/**
	 * Two columns, Gear number (highest first), numbers
	 *
	 * @param array<float> $hub
	 */
	public function getFixieTable(int $ring, int $cog, array $hub) : \PHPFUI\Table
		{
		$gear = \count($hub);

		$table = new \PHPFUI\Table();
		$table->addRow(['<b>Gear</b>', '<b>Value</b>']);

		foreach ($hub as $ratio)
			{
			$row = ["<b>{$gear}</b>", $this->computeGear($ring * $ratio, $cog)];
			$table->addRow($row);
			--$gear;
			}

		return $table;
		}

	/**
	 * @return array<float>
	 */
	public function getFrontHub() : array
		{
		return $this->frontHub;
		}

	/**
	 * Gear across (highest first) / cogs down
	 *
	 * @param array<float> $hub
	 */
	public function getHubCassetteTable(int $ring, array $hub) : \PHPFUI\Table
		{
		$gear = \count($hub);

		$table = new \PHPFUI\Table();
		$headers = ['<b>Gear/Cog</b>'];
		$gears = \count($hub);

		while ($gear)
			{
			$headers[] = "<b>{$gear}</b>";
			--$gear;
			}

		$table->addRow($headers);

		foreach ($this->cogs as $cog)
			{
			$row = ["<b>{$cog}</b>"];

			foreach ($hub as $ratio)
				{
				$row[] = $this->computeGear($ring * $ratio, $cog);
				}
			$table->addRow($row);
			}

		return $table;
		}

	public function getPageName() : string
		{
		return $this->tl ?? 'Gear Calculator';
		}

	/**
	 * @return array<float>
	 */
	public function getRearHub() : array
		{
		return $this->rearHub;
		}

	/** @return array<int> */
	public function getRings() : array
		{
		return $this->rings;
		}

	/**
	 *  Dual hubs, Mulitple Chain Rings and Multiple Cogs
	 *
	 * @param array<float> $hub1
	 * @param array<float> $hub2
	 */
	public function getRingsCassetteDualHubsTable(array $hub1, array $hub2) : \PHPFUI\Table
		{
		$this->orderHubs($hub1, $hub2);
		$table = new \PHPFUI\Table();
		$headers = ['<b>Gear</b>'];

		foreach ($this->cogs as $cog)
			{
			$headers[] = "<b>{$cog}</b>";
			}

		$table->addRow($headers);

		$gear1 = \count($hub1);

		foreach ($hub1 as $ratio1)
			{
			$gear2 = \count($hub2);

			foreach ($hub2 as $ratio2)
				{
				foreach ($this->rings as $ring)
					{
					$row = ["<b>{$gear1}-{$gear2}-{$ring}</b>"];

					foreach ($this->cogs as $cog)
						{
						$row[] = $this->computeGear($ring * $ratio1 * $ratio2, $cog);
						}
					$table->addRow($row);
					}
				--$gear2;
				}
			--$gear1;
			}

		return $table;
		}

	/**
	 * Side by side tables by gear with Chain Rings across / cogs down
	 *
	 * @param array<float> $hub
	 */
	public function getRingsCogsHub(array $hub) : \PHPFUI\Table
		{
		$gear = \count($hub);

		$table = new \PHPFUI\Table();
		$headers = ['<b>Gear</b>'];

		if (\count($this->cogs) > \count($this->rings) * \count($hub))
			{
			foreach ($hub as $ratio)
				{
				foreach ($this->rings as $ring)
					{
					$ratioString = \number_format($ratio, $this->p);
					$headers[] = "<b>{$ring}*{$ratioString}</b>";
					}
				}

			$table->addRow($headers);

			foreach ($this->cogs as $cog)
				{
				$row = ["<b>{$cog}</b>"];

				foreach ($hub as $ratio)
					{
					foreach ($this->rings as $ring)
						{
						$row[] = $this->computeGear($ring * $ratio, $cog);
						}
					}
				$table->addRow($row);
				}
			}
		else
			{
			foreach ($this->cogs as $cog)
				{
				$headers[] = "<b>{$cog}</b>";
				}

			$table->addRow($headers);

			$gear = \count($hub);

			foreach ($hub as $ratio)
				{
				foreach ($this->rings as $ring)
					{
					$row = ["<b>{$gear}-{$ring}</b>"];

					foreach ($this->cogs as $cog)
						{
						$row[] = $this->computeGear($ring * $ratio, $cog);
						}
					$table->addRow($row);
					}
				--$gear;
				}
			}

		return $table;
		}

	/**
	 * Chair Rings * gears across / gears down
	 *
	 * @param array<float> $hub1
	 * @param array<float> $hub2
	 */
	public function getRingsDualHubsTable(int $cog, array $hub1, array $hub2) : \PHPFUI\Table
		{
		$this->orderHubs($hub1, $hub2);

		$table = new \PHPFUI\Table();
		$headers = ['<b>Gear</b>'];

		$gear1 = \count($hub1);

		foreach ($hub1 as $ratio1)
			{
			foreach ($this->rings as $ring)
				{
				$ratio = \number_format($ratio1, $this->p);
				$headers[] = "<b>{$ring}*{$gear1}</b>";
				}
			--$gear1;
			}

		$table->addRow($headers);

		$gear2 = \count($hub2);

		foreach ($hub2 as $ratio2)
			{
			$row = ["<b>{$gear2}</b>"];

			foreach ($hub1 as $ratio1)
				{
				foreach ($this->rings as $ring)
					{
					$row[] = $this->computeGear($ring * $ratio1 * $ratio2, $cog);
					}
				}
			$table->addRow($row);
			--$gear2;
			}

		return $table;
		}

	/**
	 * Chair Rings across / gears down
	 *
	 * @param array<float> $hub
	 */
	public function getRingsHub(int $cog, array $hub) : \PHPFUI\Table
		{
		$gear = \count($hub);

		$table = new \PHPFUI\Table();
		$headers = ['<b>Gear</b>'];

		foreach ($this->rings as $ring)
			{
			$headers[] = "<b>{$ring}</b>";
			}

		$table->addRow($headers);

		foreach ($hub as $ratio)
			{
			$row = ["<b>{$gear}</b>"];
			--$gear;

			foreach ($this->rings as $ring)
				{
				$row[] = $this->computeGear($ring * $ratio, $cog);
				}
			$table->addRow($row);
			}

		return $table;
		}

	public function getTable() : \PHPFUI\Table
		{
		if (empty($this->rearHub) && empty($this->frontHub))
			{
			$table = $this->getTraditionTable();
			}
		elseif (! empty($this->rearHub) && ! empty($this->frontHub))
			{
			if (1 == \count($this->rings))
				{
				$ring = $this->rings[0];

				if (1 == \count($this->cogs))
					{
					// More traditional 2 x gear chart
					$table = $this->getDualHubTable($ring, $this->cogs[0], $this->rearHub, $this->frontHub);
					}
				else
					{
					// Gear across (highest first) / cogs down
					$table = $this->getDualHubCassetteTable($ring, $this->rearHub, $this->frontHub);
					}
				}
			else // we have multiple chain rings
				{
				$table = new \PHPFUI\Table();

				if (1 == \count($this->cogs))
					{
					// Chair Rings * gears across / gears down
					$table = $this->getRingsDualHubsTable($this->cogs[0], $this->rearHub, $this->frontHub);
					}
				else
					{
					$table = new \PHPFUI\Table();
					$table->addRow(['Your kidding right?']);
					// Mulitple Chain Rings and Multiple Cogs
					$table = $this->getRingsCassetteDualHubsTable($this->rearHub, $this->frontHub);
					}
				}
			}
		else // we just have one chain ring
			{
			$hub = $this->rearHub ?: $this->frontHub;

			if (1 == \count($this->rings))
				{
				$ring = $this->rings[0];

				if (1 == \count($this->cogs))
					{
					// Two columns, Gear number (highest first), numbers
					$table = $this->getFixieTable($ring, $this->cogs[0], $hub);
					}
				else
					{
					// Gear across (highest first) / cogs down
					$table = $this->getHubCassetteTable($ring, $hub);
					}
				}
			else // we have multiple chain rings
				{
				if (1 == \count($this->cogs))
					{
					// Chair Rings across / gears down
					$table = $this->getRingsHub($this->cogs[0], $hub);
					}
				else // Mulitple Chain Rings and Multiple Cogs
					{
					$table = $this->getRingsCogsHub($hub);
					}
				}
			}

		return $table;
		}

	/**
	 * No hubs, just gears
	 */
	public function getTraditionTable() : \PHPFUI\Table
		{
		$table = new \PHPFUI\Table();

		$headers = ['&nbsp;'];

		foreach ($this->rings as $ringIndex => $ring)
			{
			$headers[] = "<b>{$ring}</b>";
			}

		$table->addRow($headers);
		$rings = \count($this->rings);

		foreach ($this->cogs as $cog)
			{
			$row = ["<b>{$cog}</b>"];

			foreach ($this->rings as $ring)
				{
				$row[] = $this->computeGear((float)$ring, $cog);
				}
			$table->addRow($row);
			}

		return $table;
		}

	public function getURL() : string
		{
		$url = $this->getSchemeHost() . $_SERVER['REQUEST_URI'];

		if ($this->parameters)
			{
			$query = \strpos($url, '?');

			if ($query)
				{
				$url = \substr($url, 0, $query);
				}
			$url .= '?' . \http_build_query($this->parameters);
			}

		return $url;
		}

	public function print() : void
		{
		$config = ['format' => 'LETTER'];
		$config['mode'] = 'utf-8';
		$pdf = new \Mpdf\Mpdf($config);
		$pdf->SetMargins(15, 15, 15);
		$pdf->addPage();

		if ($this->tl)
			{
			$pdf->writeHTML("<h2>{$this->tl}</h2>");
			}
		$table = $this->getTable();
		$table->addAttribute('border', '1');
		$pdf->writeHTML("<style media='print'>table {border-collapse:collapsed;border:1px solid black;text-align:center;}</style>");
		$pdf->writeHTML($table);
		$pdf->writeHTML('<h3>Gear Differences</h3>');
		$pdf->writeHTML($this->getSequentialTable($table->getRows()));
		$pdf->Output($this->tl ?: 'GearCalculator.pdf', 'I');
		}

	/**
	 * @return array<float>
	 */
	private function getFloatArray(string $type) : array
		{
		$retVal = [];

		for ($i = 0; $i < 30; ++$i)
			{
			$field = $type . $i;

			if ($this->{$field} > 0.0)
				{
				$retVal[] = (float)$this->{$field};
				}
			}

		return $retVal;
		}

	/**
	 * @return array<float>
	 */
	private function getHubArray(string $type) : array
		{
		$parts = \explode('-', $this->{$type});

		$retVal = [];

		foreach ($parts as $part)
			{
			$retVal[] = (float)$part;
			}

		return $retVal;
		}

	/**
	 * @return array<int>
	 */
	private function getIntArray(string $type) : array
		{
		$retVal = [];

		for ($i = 0; $i < 30; ++$i)
			{
			$field = $type . $i;

			if ($this->{$field} > 0)
				{
				$retVal[] = (int)$this->{$field};
				}
			}

		return $retVal;
		}

	/**
	 * @param array<array<string>> $computedGears from the calculated gear table
	 */
	private function getSequentialTable(array $computedGears) : \PHPFUI\Table
		{
		$table = new \PHPFUI\Table();
		$table->addAttribute('border', '1');
		$headers = ['Gear', '&nbsp;&nbsp;&nbsp;Value&nbsp;&nbsp;&nbsp;', '&nbsp;&nbsp;&nbsp;Diff&nbsp;&nbsp;&nbsp;', '&nbsp;&nbsp;&nbsp;&nbsp;%&nbsp;&nbsp;&nbsp;&nbsp;', '&nbsp;Dupe&nbsp;'];
		$table->addRow($headers);

		$gears = \array_shift($computedGears);
		\array_shift($gears);
		$rows = [];

		foreach ($computedGears as $row)
			{
			$gear = \array_shift($row);

			foreach ($row as $i => $value)
				{
				$rows[$gears[$i] . '-' . $gear] = $value;
				}
			}

		\arsort($rows);

		$previousValue = (float)\current($rows);

		foreach ($rows as $index => $value)
			{
			$value = (float)$value;
			$diff = $previousValue - $value;
			$percent = $diff / $previousValue * 100.0;

			if ($diff)
				{
				$table->addRow([$index, \number_format($value, $this->p), \number_format($diff, $this->p), \number_format($percent, $this->p), $percent < 1.0 ? '****' : ' ']);
				}
			else
				{
				$table->addRow([$index, \number_format($value, $this->p), ' ', ' ', ' ']);
				}
			$previousValue = $value;
			}

		return $table;
		}

	/**
	 * Order hubs so hub1 has fewer gears
	 *
	 * @param array<float> $hub1
	 * @param array<float> $hub2
	 */
	private function orderHubs(array &$hub1, array &$hub2) : void
		{
		if (\count($hub1) > \count($hub2))
			{
			$temp = $hub1;
			$hub1 = $hub2;
			$hub2 = $temp;
			}
		}
	}
