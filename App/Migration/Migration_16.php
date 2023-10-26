<?php

namespace App\Migration;

class Migration_16 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Make By Laws in footer optional';
		}

	public function down() : bool
		{
		return true;
		}

	public function up() : bool
		{
		$settingTable = new \App\Table\Setting();
		$settingTable->save('ByLawsFile', '/pdf/By-Laws.pdf');

		return true;
		}
	}
