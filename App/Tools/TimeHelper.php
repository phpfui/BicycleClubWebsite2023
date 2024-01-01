<?php

namespace App\Tools;

/**
 * Deal with time stored as an int in minutes
 */
class TimeHelper
	{
	public static function fromString(string $timeString) : int
		{
		if (empty($timeString))
			{
			return -1;
			}
		$timeString = \str_replace('P', ' P', \strtoupper($timeString));
		$timeString = \str_replace('A', ' A', $timeString);
		$timeString = \str_replace(':', ' ', $timeString);
		$timeString = \str_replace('  ', ' ', $timeString);

		$array = \explode(' ', $timeString);
		$positions = \count($array);
		$ampm = 'AM';
		$hour = $minute = 0;

		if (\strpos($timeString, 'A') || \strpos($timeString, 'P'))
			{
			switch ($positions)
				{
				case 4:
					[$hour, $minute, $second, $ampm] = $array;

					break;

				case 3:
					[$hour, $minute, $ampm] = $array;

					break;

				case 2:
					[$hour, $ampm] = $array;

					break;

				case 1:
					$hour = (int)$timeString;

					break;
				}
			$hour = (int)$hour;

			if (12 == $hour)
				{
				$hour = 0;
				}

			if (\str_contains($ampm, 'P'))
				{
				$hour += 12;
				}
			}
		else
			{
			switch ($positions)
				{
				case 3:
					[$hour, $minute, $second] = $array;

					break;

				case 2:
					[$hour, $minute] = $array;

					break;

				case 1:
					$hour = (int)$timeString;

					break;
				}
			}
		$hour = (int)$hour;
		$minute = (int)$minute;

		if ($hour > 23 || $hour < 0 || $minute < 0 || $minute > 59)
			{
			return -1;
			}

		return $hour * 60 + $minute;
		}

	public static function relativeFormat(string $timeStamp) : string
		{
		$time = \strtotime($timeStamp);
		$diff = \time() - $time;
		$minutes = \round(($diff + 30) / 60);
		$hours = \round(($diff + 1800) / 3600);
		$days = \round(($diff + 43200) / 86400);

		if ($minutes < 60)
			{
			return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
			}

		if ($hours < 24)
			{
			return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
			}

		if ($days < 7)
			{
			return \date('l g:i a', $time);
			}

		if ($days < 360)
			{
			return \date('D M j g:i a', $time);
			}

		return \date('D M j, Y g:i a', $time);
		}

	public static function roundToInterval(string $timeString, int $interval = 15) : string
		{
		$timeInt = self::fromString($timeString);

		if ($timeInt >= 0)
			{
			$diff = $timeInt % $interval;

			if ($diff)
				{
				$timeInt = (int)($timeInt / $interval) * $interval;

				if ($diff > $interval / 2)
					{
					$timeInt += $interval;
					}
				}
			}

		return self::toString((int)$timeInt);
		}

	public static function splitTime(int $time, int &$hour, int &$minute, string &$ampm) : void
		{
		if (-1 != $time)
			{
			$hour = \floor($time / 60);
			$minute = $time % 60;

			if ($hour > 11)
				{
				$ampm = 'PM';
				$hour -= 12;
				}
			else
				{
				$ampm = 'AM';
				}

			if (0 == $hour)
				{
				$hour = 12;
				}
			}
		else
			{
			$hour = 0;
			$minute = -1;
			$ampm = '';
			}
		}

	public static function toMilitary(int $time) : string
		{
		$returnValue = '';

		if ($time >= 0)
			{
			$hour = \floor($time / 60);
			$minute = $time % 60;
			$returnValue = $hour . ':';

			if ($minute < 10)
				{
				$returnValue .= '0';
				}
			$returnValue .= $minute;
			}

		return $returnValue;
		}

	public static function toSmallTime(string $time) : string
		{
		return \str_replace([':00', ' ', 'M', 'm'], '', self::toString(self::fromString($time)));
		}

	public static function toString(int $time) : string
		{
		$returnValue = '';

		if ($time >= 0)
			{
			$hour = $minute = -1;
			$ampm = '';
			self::splitTime($time, $hour, $minute, $ampm);
			$returnValue = $hour . ':';

			if ($minute < 10)
				{
				$returnValue .= '0';
				}
			$returnValue .= $minute . ' ' . $ampm;
			}

		return \strtolower($returnValue);
		}
	}
