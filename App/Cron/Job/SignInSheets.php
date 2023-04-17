<?php

namespace App\Cron\Job;

class SignInSheets extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Send pending sign in sheets email to sign in sheet coordinator.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$memberPicker = new \App\Model\MemberPicker('Sign In Sheet Coordinator');
		$chair = $memberPicker->getMember();
		$signinSheetTable = new \App\Table\SigninSheet();
		$signinSheetTable->setWhere(new \PHPFUI\ORM\Condition('pending', 0, new \PHPFUI\ORM\Operator\GreaterThan()));

		if (\count($signinSheetTable))
			{
			$email = new \App\Tools\EMail();
			$email->setSubject('There are pending sign in sheets to approve');
			$host = $this->controller->getSchemeHost();
			$email->setBody('You can approve sign in sheets <a href="' . $host . '/SignInSheets/pending">here</a>.');
			$email->setFromMember($chair);
			$email->addToMember($chair);
			$email->setHtml();
			$email->send();
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(5, 15);
		}
	}
