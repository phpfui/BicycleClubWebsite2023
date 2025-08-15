<?php

namespace App\Migration;

class Migration_70 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Add cellPhone to Customer';
		}

	public function down() : bool
		{
		return $this->dropColumn('customer', 'cellPhone');
		}

	public function up() : bool
		{
		return $this->addColumn('customer', 'cellPhone', 'varchar(20) default ""');
		}
	}
