<?php

namespace App\Migration;

class Migration_6 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Fix table definitions';
		}

	public function down() : bool
		{
		return true;
		}

	public function up() : bool
		{
		$this->alterColumn('banner', 'startDate', 'DATE NULL DEFAULT NULL');
		$this->alterColumn('banner', 'endDate', 'DATE NULL DEFAULT NULL');
		$this->alterColumn('member', 'passwordResetExpires', 'TIMESTAMP NULL DEFAULT NULL');
		$this->alterColumn('oauthUser', 'lastLogin', 'TIMESTAMP NULL DEFAULT NULL');

		return true;
		}
	}
