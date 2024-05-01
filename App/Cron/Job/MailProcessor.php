<?php

namespace App\Cron\Job;

include PROJECT_ROOT . '/DI\functions.php';

class MailProcessor extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Process the inbox.  Run NukeMail if this is barfing to purge the first email in the inbox.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$processors = \App\Cron\EMailProcessorFactory::get();

		$imap = new \App\Model\IMAP();

		if (! $imap->valid())
			{
			return;
			}
		$numMessages = \count($imap);
		$parser = new \ZBateson\MailMimeParser\MailMimeParser();

		for ($i = 1; $i <= $numMessages; ++$i)
			{
			$tempFile = new \App\Tools\TempFile();
			$imap->saveBodyToFile($tempFile, $i);
			$processed = false;
			$message = null;

			try
				{
				$message = $parser->parse($tempFile->open('r'), false);

				foreach ($processors as $processor)
					{
					if ($processor->process($message))
						{
						$processed = true;

						break;
						}
					}
				}
			catch (\Throwable $e)
				{
				$this->controller->log_exception($e);
				}

			if (! $processed && $message)
				{
				$fallbackProcessor = new \App\Model\FallbackEmailProcessor();
				$fallbackProcessor->process($message);
				}

			if ($message)
				{
				$imap->delete((string)$i);
				}
			else
				{
				$this->controller->log_critical('unable to parse email');
				}
			}
		unset($processors);
		}

	public function willRun() : bool
		{
		return $this->controller->runningAtMinute() > 0;
		}
	}
