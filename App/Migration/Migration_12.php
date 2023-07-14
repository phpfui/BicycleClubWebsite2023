<?php

namespace App\Migration;

class Migration_12 extends \PHPFUI\ORM\Migration
	{
	/**
	 * @var array<string, int> $fields
	 */
	private array $fields = ['address' => 100, 'town' => 50, 'state' => 2, 'nearestExit' => 50];

	public function description() : string
		{
		return 'Add address, town, state and nearest exit to StartLocations';
		}

	public function down() : bool
		{
		$table = 'startLocation';

		foreach ($this->fields as $field => $size)
			{
			$this->dropColumn($table, $field);
			}

		return true;
		}

	public function up() : bool
		{
		$table = 'startLocation';

		foreach ($this->fields as $field => $size)
			{
			$this->addColumn($table, $field, "varchar({$size}) default ''");
			}

		return true;
		}
	}
