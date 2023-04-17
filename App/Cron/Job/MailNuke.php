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
		$settingTable = new \App\Table\Setting();
		$server = $settingTable->value('IMAPServer');

		if (! $server)
			{
			return;
			}
		$mbox = @\imap_open($server, $box = $settingTable->value('IMAPMailBox'), $password = $settingTable->value('IMAPPassword'));

		if (@\imap_num_msg($mbox))
			{
			@\imap_delete($mbox, '0');
			echo 'Nuked first';
			}
		else
			{
			echo 'Nothing to nuke';
			}
		echo '<br>';
		echo '<br>';
		@\imap_close($mbox, CL_EXPUNGE);
		}

	public function willRun() : bool
		{
		return false;
		}
	}
