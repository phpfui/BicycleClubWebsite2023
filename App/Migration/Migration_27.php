<?php

namespace App\Migration;

class Migration_27 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Add summary flag to Member Notices';
		}

	public function down() : bool
		{
		$this->dropColumn('memberNotice', 'summary');

		return true;
		}

	public function up() : bool
		{
		$this->addColumn('memberNotice', 'summary', 'int not null default 1');

		return true;
		}
	}
