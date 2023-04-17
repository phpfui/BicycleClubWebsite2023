<?php

namespace App\Cron\Job;

class FileBackup extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Perform a full file backup (but leaves files on server)';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		try
			{
			$backup = new \App\Model\FileBackup();
			$backup->run(PROJECT_ROOT, '/files');
			$backup->run(PROJECT_ROOT, '/config');
			$backup->run(PROJECT_ROOT, '/backups');
			$backup->run(PUBLIC_ROOT, '/images');
			$backup->run(PUBLIC_ROOT, '/pdf');
			$backup->run(PUBLIC_ROOT, '/video');
			}
		catch (\Exception $e)
			{
			$this->controller->log_exception($e->getMessage());
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(2, 30);
		}
	}
