<?php

namespace App\Migration;

class Migration_28 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Add nearestExit to addressExit for Start Locations';
		}

	public function down() : bool
		{
		$this->alterColumn('startLocation', 'addressExit', 'varchar(50)', 'nearestExit');

		return true;
		}

	public function up() : bool
		{
		$this->alterColumn('startLocation', 'nearestExit', 'varchar(255)', 'addressExit');

		return true;
		}
	}
