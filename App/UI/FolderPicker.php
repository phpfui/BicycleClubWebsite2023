<?php

namespace App\UI;

class FolderPicker extends \PHPFUI\Input\Select
	{
	public function __construct(\App\Record\Folder $currentFolder, string $name = 'folderId', string $label = 'Folder')
		{
		parent::__construct($name, $label);
		$folderTable = new \App\Table\Folder();
		$folderTable->setWhere(new \PHPFUI\ORM\Condition('folderType', $currentFolder->folderType))->addOrderBy('name');
		$this->addOption('None', '0', 0 == $currentFolder->folderId);

		foreach ($folderTable->getRecordCursor() as $folder)
			{
			$this->addOption($folder->name, $folder->folderId, $currentFolder->folderId == $folder->folderId);
			}
		}
	}
