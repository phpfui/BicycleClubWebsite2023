<?php

namespace App\Migration;

class Migration_55 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Allow RWGPS.zip to be null';
		}

	public function down() : bool
		{
		return true;
		}

	public function up() : bool
		{
		return $this->alterColumn('RWGPS', 'zip', 'varchar(10) default null');
		}
	}
