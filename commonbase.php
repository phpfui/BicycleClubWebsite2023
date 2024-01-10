<?php

\date_default_timezone_set('America/New_York');
// allow the autoloader and db to be included from any script that needs it.

if (! \defined('PROJECT_ROOT'))
	{
	\define('PROJECT_ROOT', __DIR__);
	\define('PUBLIC_ROOT', __DIR__ . '/www/');

	// allow the autoloader to be included from any script that needs it.
	function autoload(string $className) : void
		{
		$dir = (false === \strpos($className, '\\')) ? '\\NoNameSpace\\' : '\\';
		$path = \str_replace('\\', DIRECTORY_SEPARATOR, PROJECT_ROOT . $dir . "{$className}.php");

		if (\file_exists($path))
			{
			include_once $path;
			}
		}

	\spl_autoload_register('autoload');
	}

// setup error logging
\error_reporting(E_ALL);
\ini_set('error_log', PROJECT_ROOT . '/error.log');
new \App\Tools\ErrorLogging();

function emailServerName() : string
	{
	$parts = explode('.', $_SERVER['SERVER_NAME'] ?? 'localhost');
	while(\count($parts) > 2)
		{
		array_shift($parts);
		}

	return strtolower(implode('.', $parts));
	}

