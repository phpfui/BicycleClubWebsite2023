<?php

namespace App\Tools;

class SessionManager
	{
	public static function getDirectory() : string
		{
		return PROJECT_ROOT . '/session';
		}

	public static function purgeOld(int $secondsBack = 7200) : void
		{
		$endTime = \time() - $secondsBack;

		foreach (\glob(self::getDirectory() . '/sess*') as $file)
			{
			if (\filemtime($file) < $endTime)
				{
				\App\Tools\File::unlink($file);
				}
			}
		}

	public static function start() : void
		{
		try
			{
			$lifetime = 30 * 24 * 60 * 60;
			\ini_set('session.gc_maxlifetime', $lifetime);
			\ini_set('session.use_cookies', 1);
			\ini_set('session.cookie_lifetime', $lifetime);
			\ini_set('session.gc_divisor', '1');
			\ini_set('session.gc_probability', '1');
			\session_set_cookie_params($lifetime);
			\session_save_path(self::getDirectory());
			\session_start();
			\PHPFUI\Session::cycleFlash();
			}
		catch (\Exception)
			{
			$_SESSION = [];
			\session_write_close();
			\session_start();
			}
		}
	}
