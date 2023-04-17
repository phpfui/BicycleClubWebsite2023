<?php

namespace App\Cron\Job;

class CuesheetsPending extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Email the Cue Sheet Coordinator that are pending cue sheets to approve.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$cueSheetTable = new \App\Table\CueSheet();
		$cueSheetTable->setPendingCursor();

		if (\count($cueSheetTable))
			{
			$email = new \App\Tools\EMail();
			$host = $this->controller->getSchemeHost();
			$email->setBody("There are pending cue sheets that need to be reviewed\n\n" . "You can review them here: {$host}/CueSheets/pending");
			$email->setSubject('Pending cue sheets need review');
			$memberPicker = new \App\Model\MemberPicker('Web Master');
			$email->setFromMember($memberPicker->getMember());
			$email->addToMember($memberPicker->getMember('Cue Sheet Coordinator'));
			$email->send();
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(10, 30);
		}
	}
