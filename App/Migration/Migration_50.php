<?php

namespace App\Migration;

class Migration_50 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'StoreItem Folders';
		}

	public function down() : bool
		{
		$this->alterColumn('storeItem', 'folderId', 'int');
		$this->executeAlters();
		$this->renameColumn('storeItem', 'folderId', 'parent');
		$this->addColumn('storeItem', 'cut', 'char(1)');

		return $this->addColumn('storeItem', 'type', 'int');
		}

	public function up() : bool
		{
		$this->runSQL('delete from storeItem where type>0');
		$this->alterColumn('storeItem', 'parent', 'int');
		$this->executeAlters();
		$this->renameColumn('storeItem', 'parent', 'folderId');
		$this->dropColumn('storeItem', 'cut');

		return $this->dropColumn('storeItem', 'type');
		}
	}
