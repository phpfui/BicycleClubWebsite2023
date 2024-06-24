<?php

namespace App\Migration;

class Migration_47 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'PhotoFolder to generic Folder system';
		}

	public function down() : bool
		{
		$this->dropPrimaryKey('folder');
		$this->dropIndex('folder', 'folderId');
		$this->renameTable('folder', 'photoFolder');
		$this->alterColumn('photoFolder', 'folderId', 'int not null', 'photoFolderId');
		$this->dropColumn('photoFolder', 'folderType');
		$this->alterColumn('photoFolder', 'name', "varchar(255) NOT NULL DEFAULT ''", 'photoFolder');
		$this->alterColumn('photo', 'folderId', 'int not null', 'photoFolderId');
		$this->executeAlters();
		$this->addIndex('photoFolder', 'photoFolderId');

		return $this->addPrimaryKeyAutoIncrement('photoFolder');
		}

	public function up() : bool
		{
		$this->dropPrimaryKey('photoFolder');
		$this->dropIndex('photoFolder', 'photoFolderId');
		$this->renameTable('photoFolder', 'folder');
		$this->alterColumn('folder', 'photoFolderId', 'int not null', 'folderId');
		$this->addColumn('folder', 'folderType', 'int not null default "0"');
		$this->alterColumn('folder', 'photoFolder', "varchar(255) NOT NULL DEFAULT ''", 'name');
		$this->alterColumn('photo', 'photoFolderId', 'int not null', 'folderId');
		$this->executeAlters();
		$this->addIndex('folder', 'folderId');

		return $this->addPrimaryKeyAutoIncrement('folder');
		}
	}
