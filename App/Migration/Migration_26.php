<?php

namespace App\Migration;

class Migration_26 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Category name to 20 characters';
		}

	public function down() : bool
		{
		$this->alterColumn('category', 'category', 'char(2)');

		return true;
		}

	public function up() : bool
		{
		$this->alterColumn('category', 'category', 'varchar(20) not null');

		return true;
		}
	}
