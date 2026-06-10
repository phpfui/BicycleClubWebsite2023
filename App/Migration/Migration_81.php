<?php

namespace App\Migration;

class Migration_81 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Add ride.useStartLocation';
		}

	public function down() : bool
		{
		$this->dropColumn('ride', 'useStartLocation');

		return true;
		}

	public function up() : bool
		{
		$this->addColumn('ride', 'useStartLocation', 'int default "0"');

		return true;
		}
	}
