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
		$this->renameColumn('photoFolder', 'parentFolderId', 'parentId');
		$this->dropColumn('fileFolder', 'permissionId');
		$this->alterColumn('fileFolder', 'parentFolderId', 'int default 0');
		$this->renameColumn('fileFolder', 'parentFolderId', 'parentId');

		$this->executeAlters();

		$this->addIndex('photoFolder', 'parentId');

		return $this->addIndex('fileFolder', 'parentId');
		}

	public function up() : bool
		{
		$this->dropIndex('photoFolder', 'parentId');
		$this->dropIndex('fileFolder', 'parentId');

		$this->addColumn('photoFolder', 'permissionId', 'int');
		$this->alterColumn('photoFolder', 'parentId', 'int default 0');
		$this->renameColumn('photoFolder', 'parentId', 'parentFolderId');
		$this->addColumn('fileFolder', 'permissionId', 'int');
		$this->alterColumn('fileFolder', 'parentId', 'int default 0');
		$this->renameColumn('fileFolder', 'parentId', 'parentFolderId');

		$this->executeAlters();

		$this->addIndex('photoFolder', 'parentFolderId');

		return $this->addIndex('fileFolder', 'parentFolderId');
		}
	}
