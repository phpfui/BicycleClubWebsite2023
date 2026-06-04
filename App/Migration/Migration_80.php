<?php

namespace App\Migration;

class Migration_80 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Add GaEvent lastRegistrationTime';
		}

	public function down() : bool
		{
		$this->dropColumn('gaEvent', 'lastRegistrationTime');

		return true;
		}

	public function up() : bool
		{
		$this->addColumn('gaEvent', 'lastRegistrationTime', 'time');

		return true;
		}
	}
