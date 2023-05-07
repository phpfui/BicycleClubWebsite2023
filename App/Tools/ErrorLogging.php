<?php

namespace App\Tools;

class ErrorLogging
	{
	private \App\Tools\Logger $logger;

	public function __construct()
		{
		$this->logger = \App\Tools\Logger::get();
		\register_shutdown_function([$this, 'check_for_fatal']);
		\set_error_handler([$this, 'log_error'], \E_ALL);
		\set_exception_handler([$this, 'log_exception']);
		\error_reporting(\E_ALL);
		}

	/**
	 * Checks for a fatal error, work around for set_error_handler not working on fatal errors.
	 */
	public function check_for_fatal() : bool
		{
		$error = \error_get_last();

		if ($error && \E_ERROR == $error['type'])
			{
			$this->log_error($error['type'], $error['message'], $error['file'], $error['line']);
			}

		return false;
		}

	/**
	 * Error handler, passes flow over the exception logger with new ErrorException.
	 */
	public function log_error($num, $str, $file, $line, $context = null) : bool
		{
		$this->log_exception(new \ErrorException($str, 0, $num, $file, $line));

		return false;
		}

	/**
	 * Uncaught exception handler.
	 */
	public function log_exception(\Throwable $e) : void
		{
		$errorText = $e->getMessage();

		$link = ($_SERVER['SERVER_NAME'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '');

		$file = \str_replace('/', '\\', $e->getFile());
		$dir = \str_replace('/', '\\', PROJECT_ROOT . '\\');
		$file = \str_replace($dir, '', $file);

		$message = "{$errorText};\nFile: {$file}; Line: {$e->getLine()};";

		$this->sendMessage($message);
		}

	public function sendMessage(string $message, string $type = 'error') : void
		{
		try
			{
			if ($_SERVER['REQUEST_SCHEME'] ?? '' != 'https')
				{
				echo $message;
				$_SERVER['REQUEST_SCHEME'] = 'http';
				}
			$link = ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . ($_SERVER['SERVER_NAME'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '') . "\n";
			$this->logger->debug($link . $message);
			}
		catch (\Exception $e)
			{
			}
		}

	public function warning(string $message) : void
		{
		$this->sendMessage($message, 'warning');
		}
	}
