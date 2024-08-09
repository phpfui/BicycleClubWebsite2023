<?php

namespace App\Migration;

class Migration_52 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Drop unused tables';
		}

	public function down() : bool
		{
		return true;
		}

	public function up() : bool
		{
		$this->dropTable('httpRequest');
		$this->dropTable('incentive');

		return $this->dropTable('rideIncentive');
		}
	}
