<?php

namespace App\Migration;

class Migration_59 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Fix Ride Signup';
		}

	public function down() : bool
		{
		$this->runSQL('update rideSignup set status=6 where status=5');
		$this->runSQL('update rideSignup set status=5 where status=4');
		$this->runSQL('update rideSignup set status=4 where status=4');

		return true;
		}

	public function up() : bool
		{
		$this->runSQL('update rideSignup set status=2 where status=3');
		$this->runSQL('update rideSignup set status=3 where status=4');
		$this->runSQL('update rideSignup set status=4 where status=5');
		$this->runSQL('update rideSignup set status=5 where status=6');

		return true;
		}
	}
