<?php

namespace App\Migration;

class Migration_66 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Restore license field';
		}

	public function down() : bool
		{
		$this->dropColumn('member', 'license');

		return true;
		}

	public function up() : bool
		{
		$this->addColumn('member', 'license', 'char(10)');

		return true;
		}
	}
