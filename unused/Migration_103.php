<?php

namespace App\DB\Migration;

class Migration_103 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Add foreign keys';
		}

	public function down() : bool
		{
		foreach (\PHPFUI\ORM\Table::getAllTables([]) as $table)
			{
			foreach ($table->getRecord()->getRelationships() as $relationship)
				{
				if (\str_ends_with($relationship, 'Children'))
					{
					$tableName = \lcfirst(\str_replace('Children', '', $relationship));
					$field = $tableName . 'Id';
					$this->dropForeignKey($tableName, [$field]);
					}
				}
			}

		return true;
		}

	public function up() : bool
		{
		foreach (\PHPFUI\ORM\Table::getAllTables([]) as $table)
			{
			foreach ($table->getRecord()->getRelationships() as $relationship)
				{
				if (\str_ends_with($relationship, 'Children'))
					{
					$tableName = \lcfirst(\str_replace('Children', '', $relationship));
					$field = $tableName . 'Id';
					$this->addForeignKey($table->getTableName(), $tableName, [$table->getTableName() . 'Id']);
					}
				}
			}

		return true;
		}
	}
