<?php

namespace App\Migration;

class Migration_7 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'RWGPS enhancements';
		}

	public function down() : bool
		{
		$this->alterColumn('RWGPSComment', 'comment', 'varchar(255) not null');
		$this->dropIndex('RWGPSAlternate', 'RWGPSAlternateId');

		return true;
		}

	public function up() : bool
		{
		$this->alterColumn('RWGPSComment', 'comment', 'text');
		$this->addIndex('RWGPSAlternate', 'RWGPSAlternateId');

		return true;
		}
	}
