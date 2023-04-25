<?php

namespace App\Model;

class Errors
	{
	/**
	 * @var string[]
	 *
	 * @psalm-var array{0: string, 1: string}
	 */
	private array $files = [\ini_get('error_log'), '../PayPal.log'];

	/**
	 * @var string[]
	 *
	 * @psalm-var array{0: string, 1: string}
	 */
	private array $filterLines = ['IMAP', 'SSL negotiation failed', ];

	/**
	 *
	 * @psalm-return 0|positive-int
	 */
	public function deleteAll() : int
		{
		$count = 0;

		foreach ($this->files as $file)
			{
			$filename = PUBLIC_ROOT . $file;

			if (\file_exists($filename))
				{
				++$count;
				@\unlink($filename);
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

		foreach ($this->files as $file)
			{
			$filename = PUBLIC_ROOT . $file;

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
					@\unlink($filename);
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

		if ($hook && ($_SERVER['SERVER_NAME'] ?? 'localhost') != 'localhost')
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
