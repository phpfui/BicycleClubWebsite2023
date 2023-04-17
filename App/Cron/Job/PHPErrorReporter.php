<?php

namespace App\Cron\Job;

class PHPErrorReporter extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Send any PHP Errors to Slack.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$errorModel = new \App\Model\Errors();
		$errors = $errorModel->getErrors(true);

		if ($errors)
			{
			$errorModel->sendText(\implode("\n", $errors));
			}
		}

	public function willRun() : bool
		{
		return false;  // always run directly from the cron script so if another script errors, at least the error messages go out
		}
	}
