<?php

namespace App\Migration;

class Migration_55 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Allow nulls for RWGPS table';
		}

	public function down() : bool
		{
		return true;
		}

	public function up() : bool
		{
		$this->alterColumn('RWGPS', 'town', "varchar(50) DEFAULT ''");
		$this->alterColumn('RWGPS', 'description', "varchar(255) DEFAULT ''");
		$this->alterColumn('RWGPS', 'title', "varchar(255) DEFAULT ''");
		$this->alterColumn('RWGPS', 'club', "int DEFAULT '0'");
		$this->alterColumn('RWGPS', 'feetPerMile', "decimal(5,1) DEFAULT '0.0'");
		$this->alterColumn('RWGPS', 'percentPaved', "int DEFAULT '100'");

		return $this->alterColumn('RWGPS', 'zip', 'varchar(10) default null');
		}
	}
