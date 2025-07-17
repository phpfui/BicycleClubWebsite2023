<?php

namespace App\Migration;

class Migration_67 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Convert ride.mileage to float';
		}

	public function down() : bool
		{
		$this->alterColumn('ride', 'mileage', 'varchar(8)');

		return true;
		}

	public function up() : bool
		{
		$this->alterColumn('ride', 'mileage', 'float');

		return true;
		}
	}
