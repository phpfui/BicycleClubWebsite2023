<?php

namespace App\Migration;

class Migration_41 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Remove public page redirect url';
		}

	public function down() : bool
		{
		return $this->addColumn('publicPage', 'redirectUrl', 'varchar(255)');
		}

	public function up() : bool
		{
		$this->alterColumn('gaEvent', 'includeMembership', "int DEFAULT '0'");
		$this->alterColumn('forumMember', 'emailType', "int DEFAULT '0'");
		$this->alterColumn('publicPage', 'hidden', "tinyint(1) DEFAULT '0'");

		return $this->dropColumn('publicPage', 'redirectUrl');
		}
	}
