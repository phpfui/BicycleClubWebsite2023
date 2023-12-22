<?php

namespace App\Migration;

class Migration_29 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Add zip to StartLocations';
		}

	public function down() : bool
		{
		return $this->dropColumn('startLocation', 'zip');
		}

	public function up() : bool
		{
		return $this->addColumn('startLocation', 'zip', 'varchar(10)');
		}
	}
