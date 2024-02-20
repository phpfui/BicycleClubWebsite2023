<?php

namespace App\Migration;

class Migration_36 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Extend Bike Shop notes field';
		}

	public function down() : bool
		{
		return $this->alterColumn('bikeShop', 'notes', 'varchar(255)');
		}

	public function up() : bool
		{
		return $this->alterColumn('bikeShop', 'notes', 'text');
		}
	}
