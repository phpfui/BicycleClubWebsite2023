<?php

namespace App\Migration;

class Migration_14 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Member fields should allow null';
		}

	public function down() : bool
		{
		return true;
		}

	public function up() : bool
		{
		$this->alterColumn('member', 'lastName', 'varchar(50) NOT NULL');
		$this->alterColumn('member', 'firstName', 'varchar(50) NOT NULL');
		$this->alterColumn('member', 'email', 'varchar(100) NOT NULL');
		$this->alterColumn('member', 'phone', 'varchar(20) DEFAULT ""');
		$this->alterColumn('member', 'emergencyContact', 'varchar(50) DEFAULT ""');
		$this->alterColumn('member', 'emergencyPhone', 'varchar(20) DEFAULT ""');
		$this->alterColumn('member', 'cellPhone', 'varchar(20) DEFAULT ""');
		$this->executeAlters();

		foreach (['phone', 'emergencyContact', 'emergencyPhone', 'cellPhone'] as $field)
			{
			$this->runSQL("update member set {$field}='' where {$field} is null");
			}

		return true;
		}
	}
