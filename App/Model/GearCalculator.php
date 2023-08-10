<?php

namespace App\Model;

/**
 * @property string $c
 * @property string $t
 * @property string $u
 * @property string $uc
 * @property string $fh
 * @property string $rh
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
	 * @param array<string,string> $parameters
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

	public function computeGear(float $ring, float $cog) : string
		{
		if (empty($cog))
			{
			return '';
			}
		[$diameter, $iso] = \explode('~', $this->t ?? '622~28');
		$unit = $this->u ?? '0';

		switch ($unit)
			{
			case '0': // gear inches
				// the diameter of the drive wheel, times the size of the front sprocket divided by the size of the rear sprocket
				$gear = (int)$diameter * 0.0393700787 * $ring / $cog;

				return \number_format($gear, 2);

			case '1': // gear ratio
				$gear = $ring / $cog;

				return \number_format($gear, 3);

			case '2': // meters development
				$gear = (int)$diameter * M_PI * $ring / $cog / 1000 ;

				return \number_format($gear, 3);
			}

		// compute speed
		[$rpm, $units] = \explode('~', $unit);
		$gear = (int)$diameter * M_PI * $ring / $cog * (int)$rpm * 60 / 1000000;

		if ('M' == $units)
			{
			$gear *= 0.621371;
			}

		return \number_format($gear, 1) . " {$units}PH";
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
	 * @return array<float>
	 */
	public function getFrontHub() : array
		{
		return $this->frontHub;
		}

	public function getPageName() : string
		{
		return \implode('/', $this->rings) . '- ' . \implode(',', $this->cogs);
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

	public function getURL() : string
		{
		$url = $this->getSchemeHost() . $_SERVER['REQUEST_URI'];

		if ($this->parameters)
			{
			$url .= '&' . \http_build_query($this->parameters);
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
		$cogs = \explode('-', $this->c);

		$ringSizes = ['&nbsp;'];
		$boldRings = ['&nbsp;'];

		for ($i = 1; $i <= 10; ++$i)
			{
			$field = 'ring' . $i;

			if ($this->{$field} > 0)
				{
				$ringSizes[] = (int)$this->{$field};
				$boldRings[] = "<b>{$this->{$field}}</b>";
				}
			}
		$cogs = \explode('-', $this->c);
		$table = new \PHPFUI\Table();
		$table->addRow($boldRings);
		$rings = \count($ringSizes);

		foreach ($cogs as $cog)
			{
			$row = ["<b>{$cog}</b>"];

			for ($ring = 1; $ring < $rings; ++$ring)
				{
				$row[] = $this->computeGear((float)$ringSizes[$ring], (float)$cog);
				}
			$table->addRow($row);
			}
		$table->addAttribute('border', '1');
		$pdf->writeHTML("<style media='print'>table {border-collapse:collapsed;border:1px solid black;text-align:center;}</style>");
		$pdf->writeHTML($table);
		$pdf->Output('GearCalculator.pdf', 'I');
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
	}
