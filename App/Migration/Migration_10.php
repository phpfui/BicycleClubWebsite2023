<?php

namespace App\Migration;

class Migration_10 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Private ride signup';
		}

	public function down() : bool
		{
		return $this->dropColumn('member', 'showNoRideSignup');
		}

	public function up() : bool
		{
		return $this->addColumn('member', 'showNoRideSignup', 'int default 0');
		}
	}
