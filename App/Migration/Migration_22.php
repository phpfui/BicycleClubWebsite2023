<?php

namespace App\Migration;

class Migration_22 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Add ordering for GA answers';
		}

	public function down() : bool
		{
		return $this->dropColumn('gaAnswer', 'ordering');
		}

	public function up() : bool
		{
		return $this->addColumn('gaAnswer', 'ordering', 'int not null default 0');
		}
	}
