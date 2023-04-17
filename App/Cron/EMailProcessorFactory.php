<?php

namespace App\Cron;

class EMailProcessorFactory
	{
	/**
	 * @return object[]
	 *
	 * @psalm-return list<object>
	 */
	public static function get() : array
		{
		$path = __DIR__ . '/EMailProcessors/*.php';
		$processors = [];

		foreach (\glob($path) as $class)
			{
			$class = \str_replace('/', '\\', (string)$class);
			$class = \substr($class, \strrpos($class, __NAMESPACE__));
			$class = \substr($class, 0, \strpos($class, '.'));
			$processors[] = new $class();
			}

		return $processors;
		}
	}
