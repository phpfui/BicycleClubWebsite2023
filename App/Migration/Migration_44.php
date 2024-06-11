<?php

namespace App\Migration;

class Migration_44 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Discount Codes Percentage Off';
		}

	public function down() : bool
		{
		$this->dropColumn('discountCode', 'type');

		return $this->dropColumn('discountCode', 'cashOnly');
		}

	public function up() : bool
		{
		$this->alterColumn('discountCode', 'discount', 'float(10,2) not null default 0.0');
		$this->addColumn('discountCode', 'type', 'int not null default 0');

		return $this->addColumn('discountCode', 'cashOnly', 'int not null default 0');
		}
	}
