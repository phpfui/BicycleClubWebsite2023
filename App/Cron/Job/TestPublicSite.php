<?php

namespace App\Cron\Job;

class TestPublicSite extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Test the public site for errors.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$url = $this->controller->getSchemeHost();
		$scrapper = new \App\Tools\Scrapper($url);
		$scrapper->setPageDelay(100000);
		$scrapper->scrape($url);
		$scrapper->execute();
		$scrapper->testExternalLinks();
		$this->controller->setLogLevel(\App\Cron\Controller::LOG_NORMAL);

		foreach ($scrapper->getBadLinks() as $link => $reference)
			{
			$this->controller->log_normal(self::class . ": bad link {$link}\n\n{$reference}");
			}
		$errorReporter = new \App\Cron\Job\PHPErrorReporter($this->controller);
		$errorReporter->run();
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(1, 55);
		}
	}
