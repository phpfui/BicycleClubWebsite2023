<?php
// allow the autoloader and db to be included from any script that needs it.
error_reporting(E_ALL);

if (! defined('PROJECT_ROOT'))
	{
	define ('PROJECT_ROOT', __DIR__);
	define ('PUBLIC_ROOT', __DIR__ . '/www/');

	// allow the autoloader to be included from any script that needs it.
	function autoload(string $className) : void
		{
		$dir = (strpos($className, '\\') === false) ? '\\NoNameSpace\\' : '\\';
		$path = str_replace('\\', DIRECTORY_SEPARATOR, PROJECT_ROOT . $dir . "{$className}.php");

		@include_once $path;
		}

	spl_autoload_register('autoload');
	}

date_default_timezone_set('America/New_York');

