<?php

namespace App\Migration;

class Migration_3 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Reset Password field on Member';
		}

	public function down() : bool
		{
		$this->dropColumn('member', 'passwordResetExpires');

		return $this->dropColumn('member', 'passwordReset');
		}

	public function up() : bool
		{
		$this->addColumn('member', 'passwordResetExpires', 'timestamp');

		return $this->addColumn('member', 'passwordReset', 'varchar(20)');
		}
	}
