<?php

namespace App\Migration;

class Migration_74 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Public Page Start and End Dates';
		}

	public function down() : bool
		{
		$this->dropColumn('publicPage', 'startDate');

		return $this->dropColumn('publicPage', 'endDate');
		}

	public function up() : bool
		{
		$this->addColumn('publicPage', 'startDate', 'date');

		return $this->addColumn('publicPage', 'endDate', 'date');
		}
	}
