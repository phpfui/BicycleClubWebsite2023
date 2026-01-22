<?php

include '../commonbase.php';

// @mago-expect lint:function-name
// @mago-expect lint:require-namespace
function isHttps() : bool
	{
	if (\array_key_exists('HTTPS', $_SERVER) && 'on' === $_SERVER['HTTPS'])
		{
		return true;
		}

	if (\array_key_exists('SERVER_PORT', $_SERVER) && 443 === (int)$_SERVER['SERVER_PORT'])
		{
		return true;
		}

	if (\array_key_exists('HTTP_X_FORWARDED_SSL', $_SERVER) && 'on' === $_SERVER['HTTP_X_FORWARDED_SSL'])
		{
		return true;
		}

	return \array_key_exists('HTTP_X_FORWARDED_PROTO', $_SERVER) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'];
	}

$dbSettings = new \App\Settings\DB();

$htaccess = PROJECT_ROOT . '/www/.htaccess';

if (! \file_exists($htaccess))
	{
	$extension = \isHttps() ? 'prod' : 'local';
	\copy(PROJECT_ROOT . '/.htaccess.' . $extension, $htaccess);
	}

if ($dbSettings->empty())
	{
	$dbSettings->stage = 0;
	$dbSettings->setup = true;
	$dbSettings->save();
	}

if ($dbSettings->setup)
	{
	$stage = $dbSettings->stage ?? 0;
	\header('Location: /Config/wizard/' . $stage);
	}
else
	{
	\header('Location: /Home');
	}
