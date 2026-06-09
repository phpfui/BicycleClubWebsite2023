<?php

namespace App\Cron\Job;

class PurgeChunkUploads extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Purge old chunk upload files.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$now = \time();

		foreach (\glob(PROJECT_ROOT . '/files/chunkUploader/*') as $file)
			{
			if (! \str_contains((string)$file, '.gitignore'))
				{
				if (\filemtime($file) + (3600 * 24 * 7) < $now)
					{
					\App\Tools\File::unlink($file);
					}
				}
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(3, 40);
		}
	}
