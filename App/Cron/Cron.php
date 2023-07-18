<?php

namespace App\Cron;

/**
 * Runs cron jobs.
 * Put class implementation of Cron\JobInterface to run in the Cron/Job directory.
 * Set up a script to instantiate this class, then call the run method.
 * Set the controller properties to control what to do.
 *
 */
class Cron
	{
	public function __construct(private readonly \App\Cron\Controller $controller)
		{
		}

	/**
	 * Get all Job classes
	 *
	 * @return array<string,\App\Cron\BaseJob>
	 */
	public function getAllJobs() : array
		{
		$jobs = [];
		$path = __DIR__ . '/Job/*.php';

		foreach (\glob($path) as $class)
			{
			$class = \str_replace('/', '\\', (string)$class);
			$class = \substr($class, \strrpos($class, __NAMESPACE__));
			$class = \substr($class, 0, \strpos($class, '.'));
			$job = new $class($this->controller);
			$jobs[$job->getName()] = $job;
			}

		return $jobs;
		}

	/**
	 * Run all the cron jobs in Cron\Job
	 */
	public function run() : void
		{
		$format = 'D M j Y, g:i:s a';
		$this->controller->start();
		$this->controller->log_minor('Cron started at ' . \date($format) . ' and will stop at ' . \date($format, $this->controller->getEndTime()));
		$priorities = [];
		$jobs = $this->getAllJobs();

		foreach ($jobs as $cronObject)
			{
			$tempController = clone $this->controller;
			$class = $cronObject::class;
			/** @noinspection PhpUndefinedMethodInspection */
			if (! $cronObject->isDisabled() && $cronObject->willRun())
				{
				$minutesInDay = 60 * 24;
				$runCount = 0;

				for ($i = 0; $i < $minutesInDay; $i += $this->controller->getInterval())
					{
					if ($cronObject->willRun())	// @phpstan-ignore-line
						{
						++$runCount;
						}
					$tempController->increment();
					}
				$priorities[$class] = $runCount;
				$this->controller->log_minor('Will run: ' . $class . ', Priority (larger is lower): ' . $runCount);
				}
			else
				{
				$this->controller->log_minor('Not running: ' . $class);
				}
			unset($cronObject);
			}
		\asort($priorities);

		foreach ($priorities as $class => $priority)
			{
			if (! $this->controller->timedOut())
				{
				$this->controller->log_low('Running: ' . $class);
				$cronObject = new $class($this->controller);

				try
					{
					/** @noinspection PhpUndefinedMethodInspection */
					/** @noinspection PhpUndefinedMethodInspection */
					$cronObject->run();
					}
				catch (\Exception $e)
					{
					$this->controller->log_exception('Exception running: ' . $class . ': ' . $e->getMessage() . ', Line: ' . $e->getLine());
					}
				}
			}
		$end = \time();
		$this->controller->log_minor('Cron finished at ' . \date($format, $end) . ' Seconds: ' . ($end - $this->controller->getStartTime()));
		}
	}
