<?php

namespace App\Table;

class Ziptax extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\Ziptax::class;

	public static function getTaxRateForZip(string $zip) : float
		{
		$zip = \substr($zip, 0, 5);
		$sql = 'select zip_tax_rate from ziptax where zip_code=?';

		return (float)(\PHPFUI\ORM::getValue($sql, [$zip]) ?: 0.0);
		}
	}
