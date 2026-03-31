<?php

\set_time_limit(300);

// for losers not using classes
include $_SERVER['DOCUMENT_ROOT'] . '/../common.php';

if (isset($_GET['display']))
  {
	$logLevel = \App\Cron\Controller::LOG_NORMAL;
	$logger = new \App\Cron\Display();
	}
else
	{
	$logger = new \App\Tools\Logger();
	$logger->setAlwaysFlush();
	$logLevel = \App\Cron\Controller::LOG_IMPORTANT;
	}
// setup a logger and controller
$controller = new \App\Cron\Controller(5, [$logger, 'debug', ]);
$controller->setLogLevel($logLevel);
// always run error reporting no matter what.
$error = new \App\Cron\Job\PHPErrorReporter($controller);
$error->run();

if (isset($_GET['runall']))
	{
	$controller->setRunAll();
	}

// run the cron jobs if no direct option
if (empty($_GET['runnow']))
	{
	$cron = new \App\Cron\Cron($controller);
	$cron->run();
	}
else
	{
	@\ob_end_clean();
	$class = '\\App\Cron\\Job\\' . $_GET['runnow'];
	echo "<pre>Running job {$class} manually\n";
	$runnow = new $class($controller);
	$runnow->run($_GET);
	echo "\nDone Running job {$class} manually\n</pre>";
	}
