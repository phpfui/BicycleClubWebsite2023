<?php

namespace App\Cron\EMailProcessors;

class SignInSheets
	{
	/**
	 * @var string[]
	 */
	protected array $emailAddresses = ['signinsheets', 'signinsheet', 'signupsheets', 'signupsheet'];

	protected string $server;

	public function __construct()
		{
		$this->server = '@' . \emailServerName();
		}

	public function process(\ZBateson\MailMimeParser\Message $message) : bool
		{
		$to = $message->getHeader('to');

		if (! $to)
			{
			return false;
			}
		$emailAddress = '';

		foreach ($this->emailAddresses as $address)
			{
			if ($to->hasAddress($address . $this->server))
				{
				$emailAddress = $address . $this->server;

				break;
				}
			}

		if (empty($emailAddress))
			{
			return false;
			}
		$from = \App\Model\Member::cleanEmail($message->getHeaderValue('from'));
		$member = new \App\Record\Member(['email' => $from]);

		if ($member->empty())
			{
			return false;
			}
		$attachmentCount = 0;
		$email = new \App\Tools\EMail();
		$email->setToMember($member->toArray());
		$email->setHtml();
		$settingTable = new \App\Table\Setting();
		$tips = $settingTable->value('signInSheetTips');
		$clubAbbrev = $settingTable->value('clubAbbrev');
		$email->setFrom($emailAddress, $clubAbbrev . ' Sign In Sheets');

		if (! $message->getAttachmentCount())
			{
			$email->setSubject("Your email to {$emailAddress} did not have an attachment");
			$email->setBody('To submit a signin sheet, you need to attach it to the email.<p>' . $tips);
			$email->send();

			return true;
			}
		$fileModel = new \App\Model\SignInSheetFiles();
		$badExtensions = [];

		foreach ($message->getAllAttachmentParts() as $mimePart)
			{
			$attachmentHeader = $mimePart->getHeader('Content-Disposition');

			if (\strpos((string)$attachmentHeader, 'attachment;') || \strpos((string)$attachmentHeader, 'inline;'))
				{
				$fileName = \substr((string)$attachmentHeader, \strpos((string)$attachmentHeader, 'name=') + 5);
				$fileName = \str_replace(['"', "'"], '', $fileName);
				$extIndex = \strpos($fileName, '.');
				$ext = '';

				if ($extIndex)
					{
					$ext = \strtolower(\substr($fileName, $extIndex));

					if (\in_array($ext, \App\Model\SignInSheet::validExtensions()))
						{
						$sheet = new \App\Record\SigninSheet();
						$sheet->pending = 1;
						$sheet->ext = $ext;
						$sheet->member = $member;
						$sheet->dateAdded = \App\Tools\Date::todayString();
						$sheetId = $sheet->insert();
						$destFileName = $fileModel->getPath() . $sheetId . $ext;
						$mimePart->saveContent($destFileName);
						++$attachmentCount;
						}
					else
						{
						$badExtensions[] = $ext;
						}
					}
				else
					{
					$badExtensions[] = $ext;
					}
				}
			}
		$email->setSubject('Thanks for submitting your sign in sheets.');
		$body = '';

		if ($attachmentCount)
			{
			$body .= '<p>You submitted ' . $attachmentCount . ' sign in sheet' . ($attachmentCount > 1 ? 's' : '') . '. We will process them shortly.</p>';
			}

		if ($badExtensions)
			{
			$body .= '<p>The following extensions are not accepted for sign in sheets:<ul>';

			foreach ($badExtensions as $ext)
				{
				$body .= "<li>{$ext}</li>";
				}
			$body .= '</ul><p>Please change them to one of the following valid extensions and resubmit:<ul>';

			foreach (\App\Model\SignInSheet::validExtensions() as $ext)
				{
				$body .= "<li>{$ext}</li>";
				}
			$body .= '</ul></p>';
			}
		$email->setBody($body . $tips);
		$email->setHtml();
		$email->send();

		return true;
		}
	}
