<?php

namespace App\Migration;

class Migration_35 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Add Assistant Leader Types';
		}

	public function down() : bool
		{
		$this->addColumn('rideSignup', 'firstRide', 'int default null');
		$this->addColumn('rideSignup', 'firstRideInCategory', 'int default null');
		$this->dropColumn('assistantLeader', 'assistantLeaderTypeId');

		return $this->dropTable('assistantLeaderType');
		}

	public function up() : bool
		{
		$this->dropColumn('rideSignup', 'firstRide');
		$this->dropColumn('rideSignup', 'firstRideInCategory');
		$this->runSQL('CREATE table assistantLeaderType (assistantLeaderTypeId int NOT NULL AUTO_INCREMENT, name varchar(100) not null,volunteerPoints int not null default 0,PRIMARY KEY (`assistantLeaderTypeId`))');

		return $this->addColumn('assistantLeader', 'assistantLeaderTypeId', 'int');
		}
	}
