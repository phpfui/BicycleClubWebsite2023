<?php

namespace App\UI;

class MonthSubNav implements \Stringable
	{
	private readonly string $url;

	public function __construct(string $url, private readonly int $selectedMonth = 0)
		{
		$url = \explode('/', $url);
		$month = (int)\end($url);

		if ($month >= 1 && $month <= 12)
			{
			\array_pop($url);
			}
		$this->url = \implode('/', $url);
		}

	public function __toString() : string
		{
		$subnav = new \App\UI\SubNav();
		$date = new \DateTime();

		for ($i = 1; $i <= 12; ++$i)
			{
			$date->setDate(2000, $i, 1);
			$subnav->addTab($this->url . '/' . $i, $date->format('M'), $i == $this->selectedMonth);
			}

		return (string)$subnav;
		}
	}
