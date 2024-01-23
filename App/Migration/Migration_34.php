<?php

namespace App\Migration;

class Migration_34 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Change Leaders permissionmenu to Ride Leaders';
		}

	public function down() : bool
		{
		return $this->runSQL('update permission set menu="Leaders" where menu="Ride Leaders"');
		}

	public function up() : bool
		{
		return $this->runSQL('update permission set menu="Ride Leaders" where menu="Leaders"');
		}
	}
