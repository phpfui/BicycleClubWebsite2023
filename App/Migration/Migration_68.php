<?php

namespace App\Migration;

class Migration_68 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Allow RWGPS routes over 99.99 miles';
		}

	public function down() : bool
		{
		return true;
		}

	public function up() : bool
		{
		$this->alterColumn('RWGPS', 'miles', 'decimal(10,2)');
		$this->alterColumn('RWGPS', 'km', 'decimal(10,2)');
		$this->executeAlters();

		$model = new \App\Model\RideWithGPS();
		$rwgpsTable = new \App\Table\RWGPS();
		$rwgpsTable->setWhere(new \PHPFUI\ORM\Condition('miles', 99.0, new \PHPFUI\ORM\Operator\GreaterThan()));

		foreach ($rwgpsTable->getRecordCursor() as $rwgps)
			{
			$model->scrape($rwgps, true);
			$rwgps->update();
			}

		return true;
		}
	}
