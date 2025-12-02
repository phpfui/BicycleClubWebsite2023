<?php

namespace App\Migration;

class Migration_51 extends \PHPFUI\ORM\Migration
	{
	public function description() : string
		{
		return 'Video Folders';
		}

	public function down() : bool
		{
		$this->dropTable('videoType');

		$this->runSQL('CREATE TABLE `videoType` (`videoTypeId` int NOT NULL AUTO_INCREMENT,`name` varchar(70) DEFAULT "", PRIMARY KEY (`videoTypeId`))');
		$videos = \PHPFUI\ORM::getDataObjectCursor('select distinct folderId,name from folder where folderType=?', [\App\Enum\FolderType::VIDEO->value]);

		foreach ($videos as $video)
			{
			$this->runSQL('insert into `videotype` (videoTypeId,name) values (?,?)', [$video->folderId, $video->name]);
			}

		$this->runSQL('delete from `folder` where folderType=3');

		$this->alterColumn('video', 'memberId', 'int');
		$this->executeAlters();
		$this->renameColumn('video', 'memberId', 'editor');
		$this->alterColumn('video', 'folderId', 'int');
		$this->executeAlters();

		return $this->renameColumn('video', 'folderId', 'videoTypeId');
		}

	public function up() : bool
		{
		$videos = \PHPFUI\ORM::getDataObjectCursor('select * from video');

		foreach ($videos as $video)
			{
			if (! $video->fileName)
				{
				continue;
				}
			$parts = \explode('.', $video->fileName);
			$fileName = $video->videoId . '.' . \uniqid(more_entropy:true) . '.' . \array_pop($parts);
			$this->runSQL('update `video` set fileName=? where videoId=?', [$fileName, $video->videoId]);
			}

		$videoTypes = \PHPFUI\ORM::getDataObjectCursor('select * from videoType');

		foreach ($videoTypes as $videoType)
			{
			$folder = new \App\Record\Folder();
			$folder->folderType = \App\Enum\FolderType::VIDEO;
			$folder->parentFolderId = 0;
			$folder->name = $videoType->name;
			$this->runSQL('update `video` set videoTypeId=? where videoTypeId=?', [$folder->insert(), $videoType->videoTypeId]);
			}

		$this->alterColumn('video', 'videoTypeId', 'int');
		$this->executeAlters();
		$this->renameColumn('video', 'videoTypeId', 'folderId');
		$this->alterColumn('video', 'editor', 'int');
		$this->executeAlters();
		$this->renameColumn('video', 'editor', 'memberId');

		return $this->dropTable('videoType');
		}
	}
