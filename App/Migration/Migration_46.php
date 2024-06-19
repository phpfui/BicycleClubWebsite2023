<?php

namespace App\Migration;

class Migration_46 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Require Event comments';
		}

	public function down() : bool
		{
		return $this->dropColumn('event', 'requireComment');
		}

	public function up() : bool
		{
		return $this->addColumn('event', 'requireComment', 'int not null default 0');
		}
	}
