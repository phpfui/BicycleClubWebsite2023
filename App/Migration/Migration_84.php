<?php

namespace App\Migration;

class Migration_84 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Publicly viewable folders';
		}

	public function down() : bool
		{
		return $this->dropColumn('folder', 'public');
		}

	public function up() : bool
		{
		return $this->addColumn('folder', 'public', 'int NOT NULL DEFAULT "0"');
		}
	}
