<?php

namespace App\Migration;

class Migration_25 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Add abbreviated field definitions to gaOption and gaSelection tables';
		}

	public function down() : bool
		{
		$this->dropColumn('gaOption', 'csvField');
		$this->dropColumn('gaSelection', 'csvValue');

		return true;
		}

	public function up() : bool
		{
		$this->addColumn('gaOption', 'csvField', 'varchar(20)');
		$this->addColumn('gaSelection', 'csvValue', 'varchar(20)');

		return true;
		}
	}
