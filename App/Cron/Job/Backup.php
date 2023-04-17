<?php

namespace App\Cron\Job;

class Backup extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Perform a full database backup (but leaves files on server)';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		try
			{
			$backup = new \App\Model\Backup();
			$backup->run(isset($parameters['schema']), $parameters['name'] ?? 'backup');
			}
		catch (\Exception $e)
			{
			$this->controller->log_exception($e->getMessage());
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(1, 30);
		}
	}
