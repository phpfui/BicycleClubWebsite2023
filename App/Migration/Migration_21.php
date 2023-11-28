<?php

namespace App\Migration;

class Migration_21 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Add headerContent.name field';
		}

	public function down() : bool
		{
		return $this->dropColumn('headerContent', 'name');
		}

	public function up() : bool
		{
		$this->alterColumn('headerContent', 'css', 'mediumtext');

		return $this->addColumn('headerContent', 'name', 'varchar(100)');
		}
	}
