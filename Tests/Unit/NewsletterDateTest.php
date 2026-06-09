<?php

namespace Tests\Unit;

class NewsletterDateTest extends \PHPUnit\Framework\TestCase
  {
	public function testDateStrings() : void
		{
		$datePairs = [
			'2026-04-15' => 'Plain Spoken - April 15 2026.pdf',
			'2025-10-15' => 'Plain Spoken - October 15 2025.pdf',
			'2026-05-17' => 'WCC_Plain_Spoken_2026-05-17.pdf',
			'2026-03-15' => 'WCC_Plain_Spoken_2026-03-15.pdf',
			'2025-12-15' => 'Plain Spoken - December 15 2025.pdf',
			'2026-05-17' => 'PlainSpoken-May172026.pdf',
			//			'' => '',
		];

		foreach ($datePairs as $date => $fileName)
			{
			$this->assertEquals($date, \App\Model\Newsletter::getDate($fileName), "{$fileName} does not resolve to {$date}");
			}
		}
	}
