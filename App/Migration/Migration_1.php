<?php

namespace App\Migration;

class Migration_1 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Add permission groups to file and photo folders';
		}

	public function down() : bool
		{
		$this->dropIndex('photoFolder', 'parentFolderId');
		$this->dropIndex('fileFolder', 'parentFolderId');

		$this->dropColumn('photoFolder', 'permissionId');
		$this->alterColumn('photoFolder', 'parentFolderId', 'int default 0');
		$this->executeAlters();
		$this->renameColumn('photoFolder', 'parentFolderId', 'parentId');
		$this->executeAlters();
		$this->dropColumn('fileFolder', 'permissionId');
		$this->executeAlters();
		$this->alterColumn('fileFolder', 'parentFolderId', 'int default 0');
		$this->executeAlters();
		$this->renameColumn('fileFolder', 'parentFolderId', 'parentId');
		$this->executeAlters();

		return $this->addIndex('fileFolder', 'parentId');
		}

	public function up() : bool
		{
		$this->dropIndex('photoFolder', 'parentId');
		$this->dropIndex('fileFolder', 'parentId');

		$this->addColumn('photoFolder', 'permissionId', 'int');
		$this->alterColumn('photoFolder', 'parentId', 'int default 0');
		$this->executeAlters();
		$this->renameColumn('photoFolder', 'parentId', 'parentFolderId');
		$this->executeAlters();
		$this->addColumn('fileFolder', 'permissionId', 'int');
		$this->alterColumn('fileFolder', 'parentId', 'int default 0');
		$this->executeAlters();
		$this->renameColumn('fileFolder', 'parentId', 'parentFolderId');
		$this->executeAlters();

		$this->addIndex('photoFolder', 'parentFolderId');

		return $this->addIndex('fileFolder', 'parentFolderId');
		}
	}
