<?php

namespace App\Tools;

class Logger
	{
	private bool $alwaysFlush = false;

	private string $content = '<pre>';

	/** @var array<string> */
	private array $ignoredErrors = ['exif_read_data'];

	private static ?self $logger = null;

	private bool $timeStamp = false;

	/**
	 * @param bool $delete delete old file if true, default false
	 */
	public function __construct(bool $delete = false)
		{
		if ($delete)
			{
			$filename = $this->getFileName();

			\App\Tools\File::unlink($filename);
			}
		}

	public function __destruct()
		{
		$this->flush();
		}

	public function backTrace(string $message = '') : static
		{
		$this->log($message . "\n" . $this->formatTrace(\debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)));

		return $this;
		}

	/**
	 * output debug message
	 *
	 * @param mixed $message message to output\
	 * @param string $location extra debug message
	 *
	 */
	public function debug(mixed $message, string $location = '') : static
		{
		$bt = \debug_backtrace();

		if (! isset($bt[0]['file']))
			{
			if (\strlen($location))
				{
				$location .= ': ';
				}
			$this->log($location . $this->print($message));

			return $this;
			}
		$src = \file($bt[0]['file']);
		$line = $src[$bt[0]['line'] - 1] ?? '';
		\preg_match('#' . __FUNCTION__ . '\((.+)\)#', $line, $match);
		$max = \strlen($match[1] ?? 0);
		$varname = '';
		$c = 0;

		for ($i = 0; $i < $max; $i++)
			{
			if ('(' == ($match[1][$i] ?? ''))
				{
				$c++;
				}
			elseif (')' == ($match[1][$i] ?? ''))
				{
				$c--;
				}

			if ($c < 0)
				{
				break;
				}
			$varname .= ($match[1][$i] ?? '');
			}
		$at = '';

		if (\strlen($location))
			{
			$at = "({$location})";
			}

		return $this->log("{$varname} {$at}: " . $this->print($message));
		}

	/**
	 * output exception with full stack trace
	 *
	 * @param \Exception $e the exception to be logged
	 * @param string $message message to output
	 */
	public function exception(\Exception $e, string $message = '') : Logger
		{
		if (empty($message))
			{
			$message = $_SERVER['REQUEST_URI'] . ' threw exception';
			}
		$output = $this->print($message) . "\n";

		do
			{
			$output .= "{$e->getMessage()}\nin file {$e->getFile()} at line {$e->getLine()}\n";
			$output .= $this->formatTrace($e->getTrace());
			}
		while ($e = $e->getPrevious());

		return $this->log($output);
		}

	public function flush() : static
		{
		if ('<pre>' != $this->content)
			{
			\error_log($this->content);
			$this->content = '';
			}

		return $this;
		}

	/**
	 * returns a html formatted string with full stack trace
	 *
	 * @param array<array<string,mixed>> $stack stack trace array from exception or debug_backtrace
	 * @param int $showParameters level 0 = none, 1 = scalar, 2 = all
	 */
	public function formatTrace(array $stack, int $showParameters = 2) : string
		{
		$depth = 0;
		$output = '';

		foreach ($stack as $trace)
			{
			$output .= "#{$depth} ";

			if (! empty($trace['file']))
				{
				$output .= "{$trace['file']}({$trace['line']}): ";
				}

			if (! empty($trace['class']))
				{
				$output .= $trace['class'] . $trace['type'];
				}
			$output .= $trace['function'] . '(';
			$comma = '';

			if ($showParameters && ! empty($trace['args']))
				{
				foreach ($trace['args'] as $arg)
					{
					if (1 == $showParameters)
						{
						if (\is_array($arg))
							{
							$output .= $comma . 'Array';
							}
						elseif (\is_object($arg))
							{
							$output .= $comma . 'Object';
							}
						}
					else
						{
						$output .= $comma . $this->print($arg);
						}
					$comma = ', ';
					}
				}
			$output .= ");\n";
			++$depth;
			}

		return $output;
		}

	public static function get() : Logger
		{
		if (! self::$logger)
			{
			self::$logger = new \App\Tools\Logger();
			self::$logger->setAlwaysFlush();
			}

		return self::$logger;
		}

	public function getFileName() : string
		{
		return \ini_get('error_log');
		}

	public function outputNow() : static
		{
		$this->flush();

		return $this;
		}

	public function setAlwaysFlush(bool $alwaysFlush = true) : static
		{
		$this->alwaysFlush = $alwaysFlush;

		return $this;
		}

	public function setTimeStamp(bool $timeStamp = false) : static
		{
		$this->timeStamp = $timeStamp;

		return $this;
		}

	/**
	 * 	 * log the information to the appropriate location
	 * 	 *
	 *
	 * @param mixed $message message to output
	 *
	 */
	protected function log(mixed $message) : static
		{
		$error = $this->print($message);

		foreach ($this->ignoredErrors as $ignore)
			{
			if (\str_contains($error, $ignore))
				{
				return $this;
				}
			}

		if ($this->timeStamp)
			{
			[$microtime, $time] = \explode(' ', \microtime());
			$this->content .= \date('h:i:s A ', (int)$time) . $microtime . "\n";
			}
		$this->content .= $error . "\n";

		if ($this->alwaysFlush)
			{
			$this->flush();
			}

		return $this;
		}

	private function print(mixed $object) : string
		{
		$return = \print_r($object, true);

		$_SERVER['SERVER_ADDR'] ??= '::1';
		$_SERVER['HTTP_HOST'] ??= 'localhost';

		if ('localhost' == $_SERVER['HTTP_HOST'] || '::1' == $_SERVER['SERVER_ADDR'])
			{
			return $return;
			}

		foreach (['secret', 'password'] as $crypt)
			{
			while (false !== ($pos = \stripos($return, $crypt)))
				{
				$eol = \strpos($return, "\n", $pos);

				if (false === $eol)
					{
					$eol = \strlen($return);
					}

				for ($i = $pos; $i < $eol; ++$i)
					{
					$return[$i] = 'X';
					}
				}
			}

		return $return;
		}
	}
