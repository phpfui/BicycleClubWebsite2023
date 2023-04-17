<?php

namespace App\Cron\Job;

class TestSite extends \App\Cron\BaseJob
	{
	private int $endHour = 5;

	private int $startHour = 2;

	public function getDescription() : string
		{
		return 'Test the internal site for errors.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$mainMenu = new \App\View\MainMenu(new \App\Model\Permission());
		$sections = $mainMenu->getSectionURLs();

		$hour = $this->controller->runningAtHour() - $this->startHour;
		$minute = $this->controller->runningAtMinute();

		$index = $hour * (60 / $this->controller->getInterval()) + $minute / $this->controller->getInterval();

		if ($index >= \count($sections) && empty($_GET['override']) && empty($_GET['specific']) && empty($_GET['all']))
			{
			return;	// past our bedtime
			}
		$index %= \count($sections);

		if (isset($_GET['specific']))
			{
			$sections = [$_GET['specific']];
			}
		elseif (empty($_GET['all']))
			{
			$sections = [$sections[$index]];
			}

		$url = $this->controller->getSchemeHost();

		$scrapper = new \App\Tools\Scrapper($url);
		$scrapper->setPageDelay(100000);
		$settings = new \App\Settings\TestSite();
		$scrapper->login("{$url}/Home", ['email' => $settings->getUserName(),
			'password' => $settings->getPassword(),
			'SignIn' => 'Sign In', ]);

		foreach ($sections as $section)
			{
			$section = "/{$section}/";
			$scrapper->clearPageFilters();
			$scrapper->addPageFilter($section);
			$scrapper->scrape($url . $section);
			$scrapper->execute();
			$scrapper->resetInternalLinks();
			}

		$this->controller->setLogLevel(\App\Cron\Controller::LOG_NORMAL);

		// send bad links to error log
		foreach ($scrapper->getBadLinks() as $link => $reference)
			{
			$this->controller->log_normal(self::class . ": bad link {$link}\n\n{$reference}");
			}

		// process any actual errors
		$errorReporter = new \App\Cron\Job\PHPErrorReporter($this->controller);
		$errorReporter->run();
		}

	public function willRun() : bool
		{
		$hour = $this->controller->runningAtHour();

		return $hour >= $this->startHour && $hour < $this->endHour;
		}
	}
