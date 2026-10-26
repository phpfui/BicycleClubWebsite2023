<?php

namespace App\Migration;

class Migration_85 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Fixed Event defaults';
		}

	public function down() : bool
		{
		return true;
		}

	public function up() : bool
		{
		$this->alterColumn('event', 'numberReservations', 'int NOT NULL DEFAULT "2"');

		return $this->alterColumn('event', 'maxReservations', 'int NOT NULL DEFAULT "100"');
		}
	}
