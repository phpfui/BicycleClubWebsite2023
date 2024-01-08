<?php

namespace App\Migration;

class Migration_31 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Add pending status to ride table';
		}

	public function down() : bool
		{
		return $this->dropColumn('ride', 'pending');
		}

	public function up() : bool
		{
		return $this->addColumn('ride', 'pending', 'int default 0 not null');
		}
	}
