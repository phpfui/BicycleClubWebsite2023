<?php

namespace App\Migration;

class Migration_28 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Lengthen nearestExit and address for Start Locations';
		}

	public function down() : bool
		{
		$this->alterColumn('startLocation', 'address', 'varchar(100)');
		$this->alterColumn('startLocation', 'nearestExit', 'varchar(50)');

		return true;
		}

	public function up() : bool
		{
		$this->alterColumn('startLocation', 'address', 'varchar(255)');
		$this->alterColumn('startLocation', 'nearestExit', 'varchar(255)');

		return true;
		}
	}
