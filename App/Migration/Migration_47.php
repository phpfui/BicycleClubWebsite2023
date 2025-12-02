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
		$this->renameTable('folder', 'photoFolder');
		$this->alterColumn('photoFolder', 'folderId', 'int not null');
		$this->executeAlters();
		$this->renameColumn('photoFolder', 'folderId', 'photoFolderId');
		$this->dropColumn('photoFolder', 'folderType');
		$this->alterColumn('photoFolder', 'name', "varchar(255) NOT NULL DEFAULT ''");
		$this->executeAlters();
		$this->renameColumn('photoFolder', 'name', 'photoFolder');
		$this->alterColumn('photo', 'folderId', 'int not null');
		$this->executeAlters();
		$this->renameColumn('photo', 'folderId', 'photoFolderId');
		$this->alterColumn('photo', 'description', 'varchar(255)');
		$this->executeAlters();
		$this->renameColumn('photo', 'description', 'photo');
		$this->executeAlters();

		return $this->addPrimaryKeyAutoIncrement('photoFolder');
		}

	public function up() : bool
		{
		$this->dropPrimaryKey('photoFolder');
		$this->renameTable('photoFolder', 'folder');
		$this->executeAlters();
		$this->alterColumn('folder', 'photoFolderId', 'int not null');
		$this->executeAlters();
		$this->renameColumn('folder', 'photoFolderId', 'folderId');
		$this->addColumn('folder', 'folderType', 'int not null default "0"');
		$this->alterColumn('folder', 'photoFolder', "varchar(255) NOT NULL DEFAULT ''");
		$this->executeAlters();
		$this->renameColumn('folder', 'photoFolder', 'name');
		$this->executeAlters();
		$this->alterColumn('photo', 'photoFolderId', 'int not null');
		$this->executeAlters();
		$this->renameColumn('photo', 'photoFolderId', 'folderId');
		$this->executeAlters();
		$this->alterColumn('photo', 'photo', 'varchar(255)');
		$this->executeAlters();
		$this->renameColumn('photo', 'photo', 'description');
		$this->executeAlters();

		return $this->addPrimaryKeyAutoIncrement('folder');
		}
	}
