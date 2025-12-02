<?php

namespace App\Migration;

class Migration_9 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Modernize RWGPS';
		}

	public function down() : bool
		{
		$this->addColumn('RWGPS', 'status', 'int');
		$this->dropColumn('RWGPS', 'percentPaved');
		$this->dropColumn('RWGPS', 'country');
		$this->dropColumn('RWGPS', 'metersPerKm');
		$this->dropColumn('RWGPS', 'km');
		$this->dropColumn('RWGPS', 'lastSynced');
		$this->dropColumn('RWGPS', 'elevationMeters');
		$this->alterColumn('RWGPS', 'lastUpdated', 'date');
		$this->alterColumn('RWGPS', 'miles', 'decimal(4,2)');
		$this->executeAlters();
		$this->renameColumn('RWGPS', 'miles', 'mileage');
		$this->alterColumn('RWGPS', 'elevationFeet', 'int');
		$this->executeAlters();
		$this->renameColumn('RWGPS', 'elevationFeet', 'elevation');

		return true;
		}

	public function up() : bool
		{
		$this->addColumn('RWGPS', 'percentPaved', 'int not null default 100');
		$this->addColumn('RWGPS', 'country', 'varchar(255)');
		$this->addColumn('RWGPS', 'metersPerKm', 'decimal(5,2)');
		$this->addColumn('RWGPS', 'km', 'decimal(4,2)');
		$this->addColumn('RWGPS', 'elevationMeters', 'decimal(8.2)');
		$this->alterColumn('RWGPS', 'lastSynced', 'timestamp');
		$this->alterColumn('RWGPS', 'state', 'varchar(255)');
		$this->alterColumn('RWGPS', 'lastUpdated', 'timestamp');
		$this->alterColumn('RWGPS', 'mileage', 'decimal(4,2)');
		$this->executeAlters();
		$this->renameColumn('RWGPS', 'mileage', 'miles');
		$this->alterColumn('RWGPS', 'elevation', 'decimal(8.2)');
		$this->executeAlters();
		$this->renameColumn('RWGPS', 'elevation', 'elevationFeet');
		$this->dropColumn('RWGPS', 'status');

		$rwgpsTable = new \App\Table\RWGPS();
		$rwgpsTable->update(['csv' => '', 'lastUpdated' => null]);

		return true;
		}
	}
