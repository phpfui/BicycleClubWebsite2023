<?php

namespace App\Migration;

class Migration_69 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Add member discount for General Admission';
		}

	public function down() : bool
		{
		$this->dropColumn('gaEvent', 'memberDiscount');
		$this->dropColumn('gaEvent', 'registrationOpens');
		$this->alterColumn('gaEvent', 'volunteerDiscount', 'int default 0');

		return true;
		}

	public function up() : bool
		{
		$this->addColumn('gaEvent', 'registrationOpens', 'date not null');
		$this->addColumn('gaEvent', 'memberDiscount', 'decimal(7,2) default 0.00');
		$this->alterColumn('gaEvent', 'volunteerDiscount', 'decimal(7,2) default 0.00');

		return true;
		}
	}
