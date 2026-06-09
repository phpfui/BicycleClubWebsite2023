<?php

namespace App\Model;

class Newsletter
	{
	public static function getDate(string $fileName, string $monthType = 'F') : string
		{
		$originalFileName = $fileName;

		// strip extension
		$pos = \strrpos($fileName, '.');

		if ($pos)
			{
			$fileName = \substr($fileName, 0, $pos);
			}

		// strip before last _
		$pos = \strrpos($fileName, '_');

		if ($pos)
			{
			$fileName = \substr($fileName, $pos + 1);
			}

		$time = \strtotime($fileName);

		if ($time > \strtotime('1970-01-01'))
			{
			return \date('Y-m-d', $time);
			}

		// look for text month
		for ($i = 1; $i <= 12; ++$i)
			{
			$monthName = \date($monthType, \strtotime('2000-' . $i . '-01'));

			$monthPos = \stripos($fileName, $monthName);

			if (false !== $monthPos)
				{
				$fileName = \substr($fileName, $monthPos);

				$time = \strtotime($fileName);

				if ($time > \strtotime('1970-01-01'))
					{
					return \date('Y-m-d', $time);
					}

				$fileName = \substr($fileName, \strlen($monthName));

				$year = \substr($fileName, \strlen($fileName) - 4);

				$day = \substr($fileName, 0, \strlen($fileName) - 4);

				$fileName = $monthName . ' ' . $day . ' ' . $year;

				break;
				}
			}
		$time = \strtotime($fileName);

		if ($time > \strtotime('1970-01-01'))
			{
			return \date('Y-m-d', $time);
			}

		if ('F' === $monthType)
			{
			return self::getDate($originalFileName, 'M');
			}

		return '';
		}
	}
