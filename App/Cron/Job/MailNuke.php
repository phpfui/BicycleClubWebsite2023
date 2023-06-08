<?php

namespace App\Cron\Job;

class MailNuke extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Purge the email inbox of the first evil message in the box.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$imap = new \App\Model\IMAP();

		if (\count($imap))
			{
			$imap->delete('0');
			echo 'Nuked first';
			}
		else
			{
			echo 'Nothing to nuke';
			}
		}

	public function willRun() : bool
		{
		return false;
		}
	}
