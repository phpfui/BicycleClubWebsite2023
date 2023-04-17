<?php

namespace App\Cron\Job;

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
		$settingTable = new \App\Table\Setting();
		$server = $settingTable->value('IMAPServer');

		if (! $server)
			{
			return;
			}

		$mbox = @\imap_open($server, $box = $settingTable->value('IMAPMailBox'), $password = $settingTable->value('IMAPPassword'));

		if (! $mbox)
			{
			$errors = \imap_errors();
			\App\Tools\Logger::get()->debug($errors);

			return;
			}
		$numMessages = @\imap_num_msg($mbox);
		$parser = new \ZBateson\MailMimeParser\MailMimeParser();

		for ($i = 1; $i <= $numMessages; ++$i)
			{
			$tempFile = new \App\Tools\TempFile();
			\imap_savebody($mbox, $tempFile, $i);
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
				@\imap_delete($mbox, (string)$i);
				}
			else
				{
				$this->controller->log_critical('unable to parse email');
				}
			}
		@\imap_close($mbox, CL_EXPUNGE);
		unset($processors);
		}

	public function willRun() : bool
		{
		return $this->controller->runningAtMinute() > 0;
		}
	}
