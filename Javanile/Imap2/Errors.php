<?php

/*
 * This file is part of the PHP IMAP2 package.
 *
 * (c) Francesco Bianco <bianco@javanile.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Javanile\Imap2;

class Errors
  {
  protected static array $alerts = [];

  protected static array $errors = [];

  protected static string $lastError;

  public static function alerts() : array
		{
		$return = self::$alerts;

		self::$alerts = [];

		return $return;
		}

  public static function appendAlert(string $alert) : void
		{
		self::$alerts[] = $alert;
		}

  public static function appendError(string $error) : void
		{
		self::$lastError = $error;

		self::$errors[] = $error;
		}

  public static function appendErrorCanNotOpen(string $mailbox, string $error) : void
		{
		if ('{' == $mailbox[0])
			{
			$error = \preg_replace("/^AUTHENTICATE [A-Z]+\d*:\s/i", '', $error);
			//$error = preg_replace("/^([A-Z]+\d+ )(OK|NO|BAD|BYE|PREAUTH)?\s/i", '', $error);
			$error = 'Can not authenticate to IMAP server: ' . $error;
			}
		else
			{
			$error = "Can't open mailbox {$mailbox}: no such mailbox";
			}

		$this->appendError($error);
		}

  public static function badMessageNumber(array $backtrace, int $depth) : string
		{
		if (Functions::isBackportCall($backtrace, $depth))
			{
			$depth++;
			}

		return $backtrace[$depth]['function'] . '(): Bad message number in '
				 . $backtrace[$depth]['file'] . ' on line ' . $backtrace[$depth]['line'] . '. Source code';
		}

  public static function couldNotOpenStream(string $mailbox, array $backtrace, int $depth) : string
		{
		if (isset($backtrace[$depth + 1]['function']) && 'imap_open' == $backtrace[$depth + 1]['function'])
			{
			$depth++;
			}

		return $backtrace[$depth]['function'] . '(): Couldn\'t open stream ' . $mailbox
			 . ' in ' . $backtrace[$depth]['file'] . ' on line ' . $backtrace[$depth]['line'] . '. Source code';
		}

	public static function errors() : array
		{
		$return = self::$errors;

		self::$errors = [];

		return $return;
		}

	public static function lastError() : string
		{
		return self::$lastError;
		}

	public static function raiseWarning(string $warning, array $backtrace, int $depth) : void
		{
		$message = $warning . ' in ' . $backtrace[$depth]['file'] . ' on line ' . $backtrace[$depth]['line'];

		\trigger_error($message, E_USER_WARNING);
		}
	}
