<?php

namespace App\Migration;

class Migration_61 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Active status for GA Options';
		}

	public function down() : bool
		{
		$this->addColumn('member', 'license', 'char(10)');
		$this->dropColumn('gaOption', 'active');

		return true;
		}

	public function up() : bool
		{
		$this->dropColumn('member', 'license');
		$this->addColumn('gaOption', 'active', 'int default 1');

		return true;
		}
	}
