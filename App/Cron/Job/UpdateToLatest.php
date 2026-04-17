<?php

namespace App\Cron\Job;

class UpdateToLatest extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Update to the latest release if you are on a prior release.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$retries = 5;

		while ($retries-- > 0)
			{
			try
				{
				$repo = new \Gitonomy\Git\Repository(PROJECT_ROOT);
				$deployer = new \App\Model\Deploy($repo);
				$deployer->updateToLatest();
				$retries = 0;
				}
			catch (\Exception $e)
				{
				\App\Tools\Logger::get()->debug($e, 'Deploy error');
				}
			}
		}

	public function willRun() : bool
		{
		// Run every morning at 2:20, not much going on then
		return $this->controller->runAt(2, 20);
		}
	}
