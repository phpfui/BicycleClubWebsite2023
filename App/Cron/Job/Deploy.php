<?php

namespace App\Cron\Job;

class Deploy extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Deploy a specific sha1';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$repo = new \Gitonomy\Git\Repository(PROJECT_ROOT);
		$deployer = new \App\Model\Deploy($repo);

		if (isset($_GET['sha1']))
			{
			$errors = $deployer->deployTarget($_GET['sha1']);

			if ($errors)
				{
				echo '<pre>';
				\print_r($errors);
				}
			}
		else
			{
			echo 'Nothing deployed';
			}
		}

	public function willRun() : bool
		{
		return false;
		}
	}
