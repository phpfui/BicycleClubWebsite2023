<?php

namespace App\Model;

class Errors
	{
	/**
	 * @var array<string> $files
	 */
	private array $files = [];

	/**
	 * @var string[]
	 *
	 * @psalm-var array{0: string, 1: string}
	 */
	private array $filterLines = ['IMAP', 'SSL negotiation failed', ];

	public function __construct()
		{
		$this->files = [\ini_get('error_log'), PROJECT_ROOT . '/PayPal.log'];
		}

	/**
	 *
	 * @psalm-return 0|positive-int
	 */
	public function deleteAll() : int
		{
		$count = 0;

		foreach ($this->files as $filename)
			{
			if (\file_exists($filename))
				{
				++$count;
				\App\Tools\File::unlink($filename);
				}
			}

		return $count;
		}

	/**
	 * @return string[]
	 *
	 * @psalm-return list<string>
	 */
	public function getErrors(bool $delete = false) : array
		{
		$errors = [];

		if ('https' !== $_SERVER['REQUEST_SCHEME'])
			{
			return $errors;
			}

		foreach ($this->files as $filename)
			{
			if (\file_exists($filename))
				{
				$handle = \fopen($filename, 'r');

				if ($handle)
					{
					while (false !== ($line = \fgets($handle)))
						{
						foreach ($this->filterLines as $filter)
							{
							if (\str_contains($line, $filter))
								{
								$line = '';

								break;
								}
							}
						// get rid of blank lines and single character lines (most likely {} or ())
						$line = \str_replace('<pre>', '', $line);

						if (\strlen(\trim($line)) > 1)
							{
							$errors[] = $line;
							}
						}
					\fclose($handle);
					}

				if ($delete)
					{
					\App\Tools\File::unlink($filename);
					}
				}
			}

		return $errors;
		}

	public function getSlackUrl() : string
		{
		$settingTable = new \App\Table\Setting();

		return $settingTable->value('SlackErrorWebhook');
		}

	public function sendText(string $text) : void
		{
		$hook = $this->getSlackUrl();

		if ($hook)
			{
			$guzzle = new \GuzzleHttp\Client(['verify' => false, 'http_errors' => false]);
			$client = new \Maknz\Slack\Client($hook, [], $guzzle);
			$client->send("{$_SERVER['SERVER_NAME']}\n{$text}");
			}
		}

	public function setSlackUrl(string $webhook) : void
		{
		$settingTable = new \App\Table\Setting();
		$settingTable->save('SlackErrorWebhook', $webhook);
		}
	}
