<?php

namespace App\Tools;

class Config
	{
	private static ?string $directory = null;

	public static function get(string $class) : array
		{
		$file = self::getDirectory() . \str_replace('\\', '_', $class) . '.' . $_SERVER['SERVER_NAME'];

		if (\file_exists($file))
			{
			return \parse_ini_file($file);
			}

		throw new \Exception("File {$file} not found for class {$class}");
		}

	public static function getDirectory() : string
		{
		if (empty(self::$directory))
			{
			self::$directory = PROJECT_ROOT . '/config/';
			}

		return self::$directory;
		}
	}
