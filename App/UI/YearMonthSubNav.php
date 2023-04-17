<?php

namespace App\UI;

class YearMonthSubNav implements \Stringable
	{
	private readonly int |

string $latestYear;

	private readonly string $url;

	public function __construct(string $url, private readonly int $selectedYear, private readonly int $selectedMonth, private readonly int $earliestYear, int $latestYear = 0)
		{
		$url = \explode('/', $url);
		$month = (int)\end($url);

		if ($month >= 1 && $month <= 12)
			{
			\array_pop($url);
			}
		$year = (int)\end($url);

		if ($year >= 1900 && $year <= 9999)
			{
			\array_pop($url);
			}
		$this->url = \implode('/', $url);

		if (! $latestYear)
			{
			$latestYear = \App\Tools\Date::format('Y');
			}
		$this->latestYear = $latestYear;
		}

	public function __toString() : string
		{
		$yearSubNav = new \App\UI\YearSubNav($this->url, $this->selectedYear, $this->earliestYear, $this->latestYear);
		$monthSubNav = $this->selectedYear ? new \App\UI\MonthSubNav($this->url . '/' . $this->selectedYear, $this->selectedMonth) : '';

		return "{$yearSubNav}{$monthSubNav}";
		}
	}
