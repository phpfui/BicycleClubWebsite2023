<?php

namespace App\Cron;

/**
 * Interface for running cron jobs
 *
 * To use:
 *
 * Implement this interface.  Place class in the Cron\Job directory.  Control when the job will run by returning true from willRun.
 *
 */
interface JobInterface
	{
	/**
	 * Constructor should just save the controller for future use
	 */
	public function __construct(\App\Cron\Controller $controller);

	/**
	 * Return a English text description of the job
	 */
	public function getDescription() : string;

	/**
	 * Run the job.  Call the controller if you want to know when you are running.  Do NOT use current system time.
	 */
	public function run() : void;

	/**
	 * willRun will be called initially to decide if the job will run at the time specified by the controller.
	 *
	 * It will be called repeatedly with different times for a 24 hour period to determine the priority.
	 * The more often it needs to be run, the lower the priority it will have. This function should return immediately and not do DB queries
	 * or other lengthy processes.
	 *
	 * @return bool return true if the job should run at the time specified by the controller
	 *
	 */
	public function willRun() : bool;
	}
