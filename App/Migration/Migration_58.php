<?php

namespace App\Migration;

class Migration_58 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Add event blobs to text';
		}

	public function down() : bool
		{
		return true;
		}

	public function up() : bool
		{
		$this->alterColumn('event', 'additionalInfo', 'text');
		$this->alterColumn('event', 'information', 'text');
		$this->alterColumn('event', 'directionsUrl', 'varchar(255)');
		$this->alterColumn('event', 'location', 'varchar(255)');
		$this->alterColumn('event', 'title', 'varchar(255)');

		return true;
		}
	}
