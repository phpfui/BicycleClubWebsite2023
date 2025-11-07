<?php

namespace App\Migration;

class Migration_76 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Support Event Images';
		}

	public function down() : bool
		{
		$this->alterColumn('event', 'information', 'text');
		$this->alterColumn('event', 'additionalInfo', 'text');

		return true;
		}

	public function up() : bool
		{
		$this->alterColumn('event', 'information', 'mediumtext');
		$this->alterColumn('event', 'additionalInfo', 'mediumtext');

		return true;
		}
	}
