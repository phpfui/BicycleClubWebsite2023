<?php

namespace App\Model;

class Statistics
	{
	private string $attrition = '';

	private readonly \App\Table\Member $memberTable;

	private array $monthArray = [1 => 0];

	private array $perYear = [];

	private array $renewalStats = [];

	public function __construct()
		{
		$this->memberTable = new \App\Table\Member();

		for ($i = 2; $i <= 12; ++$i)
			{
			$this->monthArray[] = 0;
			}
		$this->compute();
		}

	public function getAttrition() : string
		{
		return $this->attrition;
		}

	public function getPerYear() : array
		{
		return $this->perYear;
		}

	public function getRenewals() : array
		{
		return $this->renewalStats;
		}

	/**
	 * @return int[]
	 *
	 * @psalm-return array<array-key, int>
	 */
	public function lastSignIns(int $days = 365) : array
		{
		$result = $this->memberTable->lastSignIns($days);
		$now = \time();
		$returnValue = [1 => 0,
			7 => 0,
			30 => 0,
			60 => 0,
			90 => 0,
			180 => 0,
			$days => 0, ];

		foreach ($result as $member)
			{
			$daysAgo = ($now - \strtotime((string)$member['lastLogin'])) / 86400;

			foreach ($returnValue as $bucket => &$count)
				{
				if ($daysAgo <= $bucket)
					{
					++$count;

					break;
					}
				}
			}
		// accumulate the more recent logins into the later
		$accumulate = 0;

		foreach ($returnValue as $bucket => &$count)
			{
			$temp = $count;
			$count += $accumulate;
			$accumulate += $temp;
			}

		return $returnValue;
		}

	private function compute() : void
		{
		$membersPerMonth = $this->monthArray;
		$renewalMonth = $this->monthArray;
		$newMembers = $this->monthArray;
		$expiredMonth = $this->monthArray;
		$expiredYear = [];
		$thisYear = (int)\date('Y');
		$nextYear = $thisYear + 1;

		for ($i = 1974; $i <= $nextYear; ++$i)
			{
			$expiredYear[$i] = 0;
			}
		$today = \App\Tools\Date::todayString();
		$todayMinus365 = \App\Tools\Date::todayString(-365);

		$membershipTable = new \App\Table\Membership();

		foreach ($membershipTable->getRecordCursor() as $membership)
			{
			if (! $membership->expires)
				{
				continue;
				}
			[$expyear, $expmonth, $expday] = \explode('-', (string)$membership->expires);
			$expyear = (int)$expyear;
			$expmonth = (int)$expmonth;
			$expday = (int)$expday;

			$expyear = \min($expyear, $nextYear);
			[$year, $month, $day] = \explode('-', $membership->joined ?? \date('Y-m-d'));
			$year = (int)$year;
			$month = (int)$month;
			$day = (int)$day;

			if ($membership->joined > $todayMinus365)
				{
				$newMembers[$month] += 1;
				}

			if ($membership->expires >= $today)
				{
				$renewalMonth[$expmonth] += 1;
				}
			elseif ($membership->expires >= $todayMinus365)
				{
				$expiredMonth[$expmonth] += 1;
				}

			if ($expyear > 1974)
				{
				$expiredYear[$expyear] += 1;
				}

			while ($year > 1974 && \App\Tools\Date::makeString($year, $month, 1) <= $membership->expires && $year <= $thisYear)
				{
				$index = ($year - 1970) * 12 + $month;

				if (! isset($membersPerMonth[$index]))
					{
					$membersPerMonth[$index] = 1;
					}
				else
					{
					$membersPerMonth[$index] += 1;
					}
				$month += 1;

				if ($month > 12)
					{
					$month = 1;
					$year += 1;
					}
				}
			}
		\ksort($renewalMonth);
		$totalNetGain = 0;
		$totalCurrent = 0;
		$totalExpired = 0;
		$totalJoined = 0;

		for ($key = 1; $key <= 12; ++$key)
			{
			$month = $key;
			$year = (int)($key / 12) + 1970;
			$row = [];
			$row['month'] = \App\Tools\Date::format('F', (int)\gregoriantojd($month, 1, $year));
			$row['current'] = $renewalMonth[$key];
			$row['lapsed'] = $expiredMonth[$key];
			$row['joined'] = $newMembers[$key];
			$netGain = $newMembers[$key] - $expiredMonth[$key];
			$netGainText = $netGain;

			if ($netGain < 0)
				{
				$netGainText = "({$netGain})";
				}
			$row['net'] = $netGainText;
			$this->renewalStats[] = $row;
			$totalCurrent += $renewalMonth[$key];
			$totalExpired += $expiredMonth[$key];
			$totalJoined += $newMembers[$key];
			$totalNetGain += $netGain;
			}
		$row = [];
		$row['month'] = 'Yearly Totals';
		$row['current'] = $totalCurrent;
		$row['lapsed'] = $totalExpired;
		$row['joined'] = $totalJoined;

		if ($totalNetGain < 0)
			{
			$totalNetGain = "({$totalNetGain})";
			}
		$row['net'] = $totalNetGain;

		foreach ($row as &$value)
			{
			$value = "<strong>{$value}</strong>";
			}
		$this->renewalStats[] = $row;
		unset($value);
		$percent = 100 * $totalExpired / ($totalCurrent ?: 1);
		$this->attrition = \number_format($percent, 2, '.', ',') . '%';
		\ksort($membersPerMonth);
		$membersPerYear = [];

		foreach ($membersPerMonth as $key => $value)
			{
			$year = (int)($key / 12) + 1970;

			if ($year > 1974)
				{
				if (! isset($membersPerYear[$year]))
					{
					$membersPerYear[$year] = $value;
					}
				elseif ($membersPerYear[$year] < $value)
					{
					$membersPerYear[$year] = $value;
					}
				}
			}
		\ksort($membersPerYear);

		foreach ($membersPerYear as $key => $value)
			{
			if ($key < $thisYear)
				{
				$expired = $expiredYear[$key];
				$rate = 100 * $expired / $value;

				if ($rate < 5)
					{
					$expired = '';
					$rate = 'N/A';
					}
				else
					{
					$rate = \number_format($rate, 2) . '%';
					}
				$row = [];
				$row['date'] = $key;
				$row['count'] = $value;
				$row['lapsed'] = $expired;
				$row['rate'] = $rate;
				$this->perYear[] = $row;
				}
			}
		}
	}
