<?php

namespace App\Migration;

class Migration_56 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'ride.rideStatus should not be null';
		}

	public function down() : bool
		{
		return true;
		}

	public function up() : bool
		{
		$this->runSQL('update ride set rideStatus=0 where rideStatus is null');

		return $this->alterColumn('ride', 'rideStatus', 'int not null default "0"');
		}
	}
