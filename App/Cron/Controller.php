<?php

namespace App\Cron;

/**
 * Controls cron jobs. You can specify a specific time to run, or use the default current time.
 */
class Controller
	{
	use \App\Tools\SchemeHost;

	final public const int LOG_CRITICAL = 1;

	final public const int LOG_EXCEPTION = 0;

	final public const int LOG_IMPORTANT = 2;

	final public const int LOG_LOW = 4;

	final public const int LOG_MINOR = 5;

	final public const int LOG_NORMAL = 3;

	/** @var int Unix time stamp of when the cron script should stop */
	private int $endTime;

	/** @var ?callable logger function.  Takes a string parameter */
	private $logger = null; // @mago-expect lint:property-type

	/** @var int Debug level, higher is more verbose. Default: LOG_NORMAL */
	private int $logLevel = 3;

	/** @var bool run all jobs if true */
	private bool $runAll = false;

	/** @var int Day of the month of when the cron job is running */
	private int $runDay;

	/** @var int Day of the week (Monday = 1, Sunday = 7) of when the cron job is running */
	private int $runDayOfWeek;

	/** @var int Hour (midnight = 0) of when the cron job is running */
	private int $runHour;

	/** @var int Month (Jan = 1) of when the cron job is running */
	private int $runMonth;

	/** @var int Year when the cron job is running (YYYY) */
	private int $runYear;

	private readonly \App\Table\Setting $settingTable;

	/** @var int Minutes past midnight when the cron job is running */
	private int $startMinute;

	/** @var int Unix time stamp of when the cron job started */
	private int $startTime;

	/**
	 * Make a controller.  You need to specify how often the cron script runs, in minutes.
	 *
	 * @param int $cronInterval How often the script will run, in minutes
	 * @param callable $logger Function to log status. Function takes a string
	 *
	 */
	public function __construct(private readonly int $cronInterval, ?callable $logger = null)
		{
		$this->logger = $logger;
		$this->setStartTime(\time());
		$this->settingTable = new \App\Table\Setting();
		}

	/**
	 * @return int Unix time stamp of when the cron job will end
	 */
	public function getEndTime() : int
		{
		return $this->endTime;
		}

	/**
	 * Returns the current interval that the cron job runs at
	 *
	 * @return int cron interval
	 */
	public function getInterval() : int
		{
		return $this->cronInterval;
		}

	public function getSettingTable() : \App\Table\Setting
		{
		return $this->settingTable;
		}

	/**
	 * @return int Minutes past midnight that the cron job started
	 */
	public function getStartMinute() : int
		{
		return $this->startMinute;
		}

	/**
	 * @return int Unix time stamp of when the cron job started
	 */
	public function getStartTime() : int
		{
		return $this->startTime;
		}

	/**
	 * Move to the next interval
	 *
	 * Used to interate throught 24 hours to figure out priorities.
	 */
	public function increment() : static
		{
		$this->startTime += $this->cronInterval * 60;
		$this->compute();

		return $this;
		}

	public function isDisabled(\App\Cron\BaseJob $job) : bool
		{
		return ! empty($this->settingTable->value($job->getDisabledKey()));
		}

	/**
	 * Log a message.
	 *
	 * @param int $priority Specify the priorty.  Higher is lower.
	 * @param string $message Message to log
	 */
	public function log(int $priority, string $message) : static
		{
		if ($this->logger && $priority <= $this->logLevel)
			{
			\call_user_func($this->logger, $message);
			}

		return $this;
		}

	public function log_critical(string $message) : static
		{
		return $this->log(self::LOG_CRITICAL, $message);
		}

	public function log_exception(string | \Throwable $message) : static
		{
		return $this->log(self::LOG_EXCEPTION, $message);
		}

	public function log_important(string $message) : static
		{
		return $this->log(self::LOG_IMPORTANT, $message);
		}

	public function log_low(string $message) : static
		{
		return $this->log(self::LOG_LOW, $message);
		}

	public function log_minor(string $message) : static
		{
		return $this->log(self::LOG_MINOR, $message);
		}

	public function log_normal(string $message) : static
		{
		return $this->log(self::LOG_NORMAL, $message);
		}

	/**
	 * Should job run hourly
	 *
	 * @param int $hour Hour to check
	 * @param int $minute Minute to check
	 *
	 * @return bool return true to run
	 */
	public function runAt(int $hour, int $minute) : bool
		{
		$minutesAfterMidnight = $hour * 60 + $minute;

		return $this->runAll || $minutesAfterMidnight >= $this->startMinute && $minutesAfterMidnight < $this->startMinute + $this->cronInterval;
		}

	/**
	 * Should job run on a day of the month (1-31)
	 *
	 * @param int $day Day of the month to run on
	 *
	 * @return bool return true to run
	 */
	public function runDayOfMonth(int $day) : bool
		{
		return $this->runAll || $day == $this->runDay;
		}

	/**
	 * Should job run hourly
	 *
	 * @return bool return true to run
	 */
	public function runHourly() : bool
		{
		$minutes = $this->startMinute % 60;

		return $this->runAll || $minutes < $this->cronInterval;
		}

	/**
	 * Should job run on a specific month (Jan = 1)
	 *
	 * @param int $month Month to run on
	 *
	 * @return bool return true to run
	 */
	public function runMonth(int $month) : bool
		{
		return $this->runAll || $month == $this->runMonth;
		}

	/**
	 * Job is running on this date
	 */
	public function runningAtDate() : string
		{
		return \App\Tools\Date::makeString($this->runYear, $this->runMonth, $this->runDay);
		}

	/**
	 * Job is running on this day of the month (1-31)
	 */
	public function runningAtDay() : int
		{
		return $this->runDay;
		}

	/**
	 * Job is running on this day of the week (Mon = 1, Sun = 7)
	 *
	 * @return int Mon = 1, Sun = 7
	 */
	public function runningAtDayOfWeek() : int
		{
		return $this->runDayOfWeek;
		}

	/**
	 * Job is running at this hour
	 *
	 * @return int Hour, midnight = 0
	 */
	public function runningAtHour() : int
		{
		return $this->runHour;
		}

	/**
	 * Job is running on this Julian Date
	 */
	public function runningAtJD() : int
		{
		return \gregoriantojd($this->runMonth, $this->runDay, $this->runYear);
		}

	/**
	 * Job is running at this time past the hour
	 */
	public function runningAtMinute() : int
		{
		return $this->startMinute % 60;
		}

	/**
	 * Job is running on this month (1-12)
	 */
	public function runningAtMonth() : int
		{
		return $this->runMonth;
		}

	/**
	 * Job is running during this year (YYYY)
	 */
	public function runningAtYear() : int
		{
		return $this->runYear;
		}

	/**
	 * Should job run on a day of the week.  Monday is 1, Sunday is 7
	 *
	 * @param int $dayOfWeek day of the week to run on
	 *
	 * @return bool return true to run
	 */
	public function runWeekday(int $dayOfWeek) : bool
		{
		return $this->runAll || $dayOfWeek == $this->runDayOfWeek;
		}

	public function setDisabled(\App\Cron\BaseJob $job, bool $disabled = true) : void
		{
		$key = $job->getDisabledKey();

		if ($disabled)
			{
			$this->settingTable->save($key, 1);
			}
		else
			{
			$this->settingTable->setWhere(new \PHPFUI\ORM\Condition('name', $key));
			$this->settingTable->delete();
			}
		}

	/**
	 * Set the log level.  Higher numnbers are more verbose
	 */
	public function setLogLevel(int $logLevel = 0) : static
		{
		$this->logLevel = $logLevel;

		return $this;
		}

	/**
	 * For debugging purposes
	 */
	public function setRunAll(bool $runAll = true) : static
		{
		$this->runAll = $runAll;

		return $this;
		}

	/**
	 * Specify a a time that the jobs will run at.  Great for unit testing.
	 */
	public function setStartTime(int $time) : static
		{
		$this->startTime = $time;
		$this->compute();

		return $this;
		}

	/**
	 * Start the cron job now
	 */
	public function start() : static
		{
		$this->setStartTime(\time());

		return $this;
		}

	/**
	 * Has the cron job run too long?
	 *
	 * @return bool True if the cron job should stop
	 */
	public function timedOut() : bool
		{
		return \time() >= $this->endTime;
		}

	public function toggleDisabled(\App\Cron\BaseJob $job) : void
		{
		$key = $job->getDisabledKey();
		$value = $this->settingTable->value($key);

		if (empty($value))
			{
			$this->settingTable->save($key, 1);
			}
		else
			{
			$this->settingTable->setWhere(new \PHPFUI\ORM\Condition('name', $key));
			$this->settingTable->delete();
			}
		}

	private function compute() : void
		{
		$this->endTime = $this->startTime + $this->cronInterval * 6 - 10;
		$date = \date('Y,N,j,n,G,i', $this->startTime);

		$parts = \explode(',', $date);
		$this->runYear = (int)$parts[0];
		$this->runDayOfWeek = (int)$parts[1];
		$this->runDay = (int)$parts[2];
		$this->runMonth = (int)$parts[3];
		$this->runHour = (int)$parts[4];
		$minute = (int)$parts[5];

		$this->startMinute = $this->runHour * 60 + $minute;
		}
	}
