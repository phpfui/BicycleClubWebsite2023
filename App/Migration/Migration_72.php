<?php

namespace App\Migration;

class Migration_72 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Adding more privacy settings';
		}

	public function down() : bool
		{
		$this->dropColumn('member', 'showNoSocialMedia');

		return $this->dropColumn('member', 'showNoSignin');
		}

	public function up() : bool
		{
		$this->addColumn('member', 'showNoSignin', 'int not null default 0');

		return $this->addColumn('member', 'showNoSocialMedia', 'int not null default 0');
		}
	}
