<?php

namespace App\Migration;

class Migration_48 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'FileFolder to generic Folder system';
		}

	public function down() : bool
		{
		$this->alterColumn('file', 'folderId', 'int not null', 'fileFolderId');
		$this->alterColumn('file', 'description', 'varchar(255)', 'file');
		$this->executeAlters();

		$this->runSQL("CREATE TABLE filefolder (
			fileFolderId int NOT NULL AUTO_INCREMENT,
			fileFolder varchar(255) NOT NULL DEFAULT '',
			parentFolderId int DEFAULT '0',
			permissionId int DEFAULT NULL,
			PRIMARY KEY (fileFolderId),
			KEY parentFolderId (parentFolderId));");

		$folderTable = new \App\Table\Folder();
		$folderTable->setWhere(new \PHPFUI\ORM\Condition('folderType', \App\Enum\FolderType::FILE));

		foreach ($folderTable->getRecordCursor() as $folder)
			{
			$fileFolder = new \App\DB\Migration\FileFolder();
			$fileFolder->fileFolderId = $folder->folderId;
			$fileFolder->fileFolder = $folder->name;
			$fileFolder->parentFolderId = $folder->parentFolderId;
			$fileFolder->permissionId = $folder->permissionId;
			$fileFolder->insert();
			$folder->delete();
			}

		return true;
		}

	public function up() : bool
		{
		$this->alterColumn('file', 'fileFolderId', 'int not null', 'folderId');
		$this->alterColumn('file', 'file', 'varchar(255)', 'description');
		$this->executeAlters();

		$fileTable = new \App\Table\File();
		$fileFolderTable = new \App\DB\Migration\FileFolderTable();

		foreach ($fileFolderTable->getRecordCursor() as $fileFolder)
			{
			$folder = new \App\Record\Folder();
			$folder->name = $fileFolder->fileFolder;
			$folder->parentFolderId = $fileFolder->parentFolderId;
			$folder->folderType = \App\Enum\FolderType::FILE;
			$folder->permissionId = $fileFolder->permissionId;
			$folderId = $folder->insert();
			$fileTable->setWhere(new \PHPFUI\ORM\Condition('folderId', $fileFolder->fileFolderId));
			$fileTable->update(['folderId' => $folderId]);
			}
		$this->dropTable('fileFolder');

		return true;
		}
	}
