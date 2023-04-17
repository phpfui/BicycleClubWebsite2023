<?php

namespace App\Tools;

class Date
	{
	/**
	 * Add months to a date, but does not account for dates at the end of the month that does not exist in the new month
	 */
	public static function addMonths(int $date, int $months) : int
		{
		$year = self::year($date);
		$month = self::month($date);
		$day = self::day($date);

		while ($months < 0)
			{
			--$month;

			if ($month <= 0)
				{
				$month = 12;
				--$year;
				}
			++$months;
			}

		while ($months > 0)
			{
			++$month;

			if ($month > 12)
				{
				$month = 1;
				++$year;
				}
			--$months;
			}

		return self::make($year, $month, $day);
		}

	public static function day(int $date) : string
		{
		if ($date)
			{
			return self::format('j', $date);
			}

		return '';
		}

	/**
	 * Returns the first of the month.
	 */
	public static function firstOfMonth(int $date) : int
		{
		$year = (int)self::year($date);
		$month = (int)self::month($date);

		return self::make($year, $month, 1);
		}

	/**
	 * Returns the end of the month.
	 */
	public static function endOfMonth(int $date) : int
		{
		$year = (int)self::year($date);
		$month = (int)self::month($date) + 1;

		if ($month > 12)
			{
			$month = 1;
			++$year;
			}

		return self::make($year, $month, 1) - 1;
		}

	public static function format(string $format, ?int $date = -1) : string
		{
		if (-1 == $date)
			{
			$date = self::today();
			}

		if (! empty($date))
			{
			[$month, $day, $year] = \explode('/', \jdtogregorian((int)$date));
			$dateTime = new \DateTime();
			$dateTime->setDate((int)$year, (int)$month, (int)$day);
			$dateTime->setTime(0, 0);

			return $dateTime->format($format);
			}

		return '';
		}

	public static function formatString(string $format, ?string $date = '') : string
		{
		if (! $date)
			{
			$intDate = self::today();
			}
		else
			{
			$intDate = self::fromString($date);
			}

		return self::format($format, $intDate);
		}

	/**
	 * convert from YYYY-MM-DD to julian date
	 *
	 * @return int julian date
	 */
	public static function fromString(?string $date, string $order = 'ymd') : int
		{
		$julian = (int)$date;

		if ($julian > 0 && $julian <= 3000)
			{
			$array = \explode('/', \strtolower(\str_replace(['-', '.', '\\', ' ', ], '/', $date)));

			if (3 == \count($array))
				{
				$date = self::today();
				$day = self::day($date);
				$month = self::month($date);
				$year = self::year($date);
				$order = \strtolower($order);

				for ($i = 0; $i < \strlen($order); ++$i)
					{
					switch ($order[$i])
						{
						case 'y':
							$year = $array[$i];

							break;

						case 'm':
							$month = $array[$i];

							break;

						case 'd':
							$day = $array[$i];

							break;
						}
					}

				return self::make($year, $month, $day);
				}
			}

		return $julian;
		}

	public static function getUnixTimeStamp(string $date, string $time) : int
		{
		if (empty($date) || empty($time))
			{
			return 0;
			}

		return \jdtounix(\App\Tools\Date::fromString($date)) + \App\Tools\TimeHelper::fromString($time) * 60;
		}

	public static function make(int | string $year, int | string $month, int | string $day) : int
		{
		return \gregoriantojd((int)$month, (int)$day, (int)$year);
		}

	public static function makeString(int | string $year, int | string $month, int | string $day) : string
		{
		return \sprintf('%04d', (int)$year) . '-' . \sprintf('%02d', (int)$month) . '-' . \sprintf('%02d', (int)$day);
		}

	public static function month(int $date) : string
		{
		if ($date)
			{
			return self::format('n', $date);
			}

		return '';
		}

	public static function today(int $daysDifference = 0) : int
		{
		$dateTime = new \DateTime();

		return self::make($dateTime->format('Y'), $dateTime->format('n'), $dateTime->format('j')) + $daysDifference;
		}

	public static function todayString(int $daysDifference = 0) : string
		{
		return self::toString(self::today($daysDifference));
		}

	public static function toString(?int $date = -1, string $format = 'Y-m-d') : string
		{
		return self::format($format, $date);
		}

	public static function year(int $date = -1) : string
		{
		if ($date)
			{
			return self::format('Y', $date);
			}

		return '';
		}

	/**
	 * Increment a date (if $diff is possitive) or decrement (if negative)
	 */
	public static function increment(string $baseDate, int $diff) : string
		{
		return self::toString(self::fromString($baseDate) + $diff);
		}

	/**
	 * @return int positive difference in days from $earlierDate to $laterDate
	 */
	public static function diff(string $earlierDate, string $laterDate) : int
		{
		return self::fromString($laterDate) - self::fromString($earlierDate);
		}
	}
