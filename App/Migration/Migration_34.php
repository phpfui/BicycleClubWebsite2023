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
		$setting = new \App\Record\Setting(['name' => 'signupNotifications']);
		$setting->delete();
		$setting = new \App\Record\Setting(['name' => 'cueSheetFieldName']);
		$setting->delete();

		return $this->runSQL('update permission set menu="Leaders" where menu="Ride Leaders"');
		}

	public function up() : bool
		{
		$setting = new \App\Record\Setting();
		$setting->name = 'signupNotifications';
		$setting->value = '1';
		$setting->insert();
		$setting->name = 'cueSheetFieldName';
		$setting->value = 'Cue Sheet';
		$setting->insert();

		return $this->runSQL('update permission set menu="Ride Leaders" where menu="Leaders"');
		}
	}
