<?php

namespace App\Migration;

class Migration_43 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'RWGPS lastSynced nullable';
		}

	public function down() : bool
		{
		return true;
		}

	public function up() : bool
		{
		$rideTable = new \App\Table\Ride();

		foreach ($rideTable->getRecordCursor() as $ride)
			{
			$ride->update();
			}

		return $this->alterColumn('RWGPS', 'lastSynced', 'timestamp default null');
		}
	}
