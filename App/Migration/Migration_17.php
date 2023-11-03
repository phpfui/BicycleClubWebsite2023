<?php

namespace App\Migration;

class Migration_17 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Add Blog count';
		}

	public function down() : bool
		{
		return $this->dropColumn('blog', 'count');
		}

	public function up() : bool
		{
		$this->alterColumn('blog', 'name', 'varchar(255) not null default ""');
		$this->addColumn('blog', 'count', 'int not null default 0');
		$this->executeAlters();

		return $this->runSQL('update blog set count=5');
		}
	}
