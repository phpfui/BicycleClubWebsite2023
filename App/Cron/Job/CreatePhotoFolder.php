<?php

namespace App\Cron\Job;

class CreatePhotoFolder extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Create Year Photo Folder.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$year = $this->controller->runningAtYear();
		$key = ['name' => "{$year}", 'parentFolderId' => 0];
		$folder = new \App\Record\Folder($key);

		if ($folder->empty())
			{
			$folder->setFrom($key);
			$folder->folderType = \App\Enum\FolderType::PHOTO;
			$folder->insert();
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runDayOfMonth(1) && $this->controller->runMonth(1);
		}
	}
