<?php

namespace App\Migration;

class Migration_57 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Add forum.formerMembers';
		}

	public function down() : bool
		{
		return $this->dropColumn('forum', 'formerMembers');
		}

	public function up() : bool
		{
		return $this->addColumn('forum', 'formerMembers', 'int not null default "0"');
		}
	}
