<?php

namespace App\Migration;

class Migration_39 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Add rest stop to ride tables';
		}

	public function down() : bool
		{
		return $this->dropColumn('ride', 'restStop');
		}

	public function up() : bool
		{
		$this->runSQL('delete from permission where menu like "%/%"');

		$this->alterColumn('RWGPS', 'lastSynced', 'timestamp');
		$this->alterColumn('RWGPS', 'lastUpdated', 'timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

		return $this->addColumn('ride', 'restStop', 'varchar(70)');
		}
	}
