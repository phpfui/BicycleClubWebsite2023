<?php

namespace App\Migration;

class Migration_15 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Add public page redirect url';
		}

	public function down() : bool
		{
		return $this->dropColumn('publicPage', 'redirectUrl');
		}

	public function up() : bool
		{
		return $this->addColumn('publicPage', 'redirectUrl', 'varchar(255)');
		}
	}
