<?php

namespace App\Model;

class MemberOfMonthFile extends \App\Model\TinifyImage
	{
	public function __construct()
		{
		parent::__construct('images/MOM');
		}

	/**
	 * @param array<string,mixed> $MOM
	 */
	public function getImage(array $MOM) : string
		{
		$date = \App\Tools\Date::formatString('F Y', $MOM['month']);

		return "<img alt='{$date} Member Of The Month Image' src='/images/MOM/{$MOM['memberOfMonthId']}{$MOM['fileNameExt']}'>";
		}

	public function processFile(string | int $file) : string
		{
		$this->resizeToWidth($file, 600);

		return '';
		}
	}
