<?php

namespace App\Tools;

class File
	{
	/**
	 * @param ?resource $context
	 */
	public static function mkdir(string $directory, int $permissions = 0o777, bool $recursive = false, $context = null) : bool // @mago-expect lint:parameter-type
		{
		if (! \file_exists($directory) && ! \is_dir($directory))
			{
			return \mkdir($directory, $permissions, $recursive, $context);
			}

		return false;
		}

	/**
	 * Prevent warnings on unlinking directory or missing file
	 *
	 * @param ?resource $context
	 */
	public static function unlink(string $fileName, $context = null) : bool // @mago-expect lint:parameter-type
		{
		if (\file_exists($fileName) && ! \is_dir($fileName))
			{
			return \unlink($fileName);
			}

		return false;
		}
	}
