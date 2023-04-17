<?php

namespace App\UI;

class YearSubNav implements \Stringable
	{
	private bool $alwaysShow = true;

	private bool $decades = true;

	public function __construct(private string $url, private readonly int $selectedYear, private readonly int $earliest, private int $latest = 0)
		{
		$urlArray = \explode('/', $this->url);

		$year = (int)\end($urlArray);

		if ($year >= $this->earliest && $year <= $this->latest)
			{
			\array_pop($urlArray);
			}
		$this->url = \implode('/', $urlArray);

		if (! $this->latest)
			{
			$this->latest = (int)\App\Tools\Date::format('Y');
			}
		}

	public function __toString() : string
		{
		if (! $this->alwaysShow && $this->latest == $this->earliest)
			{
			return '';
			}

		$subnav = new \App\UI\SubNav();

		// Need at least 11 years to make decade code worth displaying
		if ($this->decades && ($this->latest - $this->earliest) > 10)
			{
			$decades = [];

			for ($year = $this->latest; $year >= $this->earliest; --$year)
				{
				$yearString = "{$year}";
				$century = (int)\substr($yearString, 0, 2) * 100;
				$decade = (int)\substr($yearString, 2, 1) * 10 + $century;

				if (! isset($decades[$decade]))
					{
					$decades[$decade] = $decade;
					}

				if ($year == $this->selectedYear)
					{
					$decades[$decade] = 0;
					}
				}

			foreach ($decades as $decade => $year)
				{
				if ($year)
					{
					$mostRecentYear = $year + 9;

					if ($mostRecentYear > $this->latest)
						{
						$mostRecentYear = $this->latest;
						}
					$subnav->addTab("{$this->url}/{$mostRecentYear}", "<b>{$year}s</b>");
					}
				else
					{
					for ($i = $decade + 9; $i >= $decade; --$i)
						{
						if ($i >= $this->earliest && $i <= $this->latest)
							{
							$subnav->addTab("{$this->url}/{$i}", (string)$i, $this->selectedYear == $i);
							}
						}
					}
				}
			}
		else
			{
			for ($year = $this->latest; $year >= $this->earliest; --$year)
				{
				$subnav->addTab($this->url . "/{$year}", (string)$year, $year == $this->selectedYear);
				}
			}

		return (string)$subnav;
		}

	public function alwaysShow(bool $show = true) : static
		{
		$this->alwaysShow = $show;

		return $this;
		}

	public function setDecades(bool $decades = true) : static
		{
		$this->decades = $decades;

		return $this;
		}
	}
