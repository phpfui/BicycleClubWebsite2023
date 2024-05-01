<?php

namespace App\Cron\EMailProcessors;

class Forums
	{
	/**
	 * @var array<string, bool> $dups
	 */
	protected array $dups = [];

	private readonly \App\Model\Forum $model;

	public function __construct()
		{
		$this->model = new \App\Model\Forum();
		}

	public function process(\ZBateson\MailMimeParser\Message $message) : bool
		{
		$to = $message->getHeader('to');
		$cc = $message->getHeader('cc');
		$validEmail = false;

		$forumTable = new \App\Table\Forum();
		$forumTable->addOrderBy('name');

		foreach ($forumTable->getRecordCursor() as $forum)
			{
			$emailAddress = $forum->email . '@' . \emailServerName();

			if (($to && $to->hasAddress($emailAddress)) || ($cc && $cc->hasAddress($emailAddress)))
				{
				$this->processMessage($message, $forum);
				$validEmail = true;
				}
			}

		return $validEmail;
		}

	private function processMessage(\ZBateson\MailMimeParser\Message $message, \App\Record\Forum $forum) : void
		{
		$from = \App\Model\Member::cleanEmail($message->getHeaderValue('from'));
		$members = $this->model->getMembers($forum, ['email' => $from]);

		if (! \count($members))
			{
			// could be additional email address or white list, let model figure it out
			$member = $this->model->isAdditionalEmail($from, $forum);

			if (! $member)
				{
				$email = new \App\Tools\EMail();
				$settingTable = new \App\Table\Setting();
				$clubAbbrev = $settingTable->value('clubAbbrev');
				$email->setSubject("You are not a member of the {$clubAbbrev} {$forum->name} forum.");
				$email->addTo($from);
				$email->setHTML();
				$homePage = $settingTable->value('homePage');
				$body = "You must be a member of the {$clubAbbrev} {$forum->name} forum to send emails.<p>You can <a href='{$homePage}/Forums/home/{$forum->forumId}'>join here</a><p>If you have further questions, please reply to this email.<p>Thank you<br>The web master";
				$email->setBody($body);
				$email->send();

				return;
				}
			}
		else
			{
			$member = $members->current();
			}
		$title = $message->getHeaderValue('subject') ?? '';
		$date = $message->getHeaderValue('date');
		$posted = \date('Y-m-d H:i:s', \strtotime($date));
		$text = $message->getTextContent() ?? '';
		$html = \App\Tools\TextHelper::htmlentities($message->getHtmlContent() ?? '');

		if (! \strlen($text) && \strlen($html))
			{
			$text = \Soundasleep\Html2Text::convert($html, ['drop_links' => 'href', 'ignore_errors' => true]);
			}
		$sha1 = \sha1($forum->email . $html . $text);

		if (isset($this->dups[$sha1]))
			{
			return;
			}
		$this->dups[$sha1] = true;
		// to do, set post to accept non-member with email, first and last name set
		$post = ['forumId' => $forum->forumId, 'memberId' => $member['memberId'], 'firstName' => $member['firstName'],
			'lastName' => $member['lastName'], 'email' => $member['email'], 'title' => $title,
			'posted' => $posted, 'htmlMessage' => $html, 'textMessage' => $text, ];
		$forumMessageId = $this->model->post($post);

		if ($message->getAttachmentCount() && $forum->attachments)
			{
			$email = $this->model->getEmail();
			$fileModel = new \App\Model\ForumAttachmentFiles();

			foreach ($message->getAllAttachmentParts() as $mimePart)
				{
				$attachmentHeader = $mimePart->getHeader('Content-Disposition');

				if ($attachmentHeader && (\strpos((string)$attachmentHeader, 'attachment;') || \strpos((string)$attachmentHeader, 'inline;')))
					{
					$parts = [];

					foreach (\explode(';', (string)$attachmentHeader) as $part)
						{
						$sections = \explode('=', \str_replace('"', '', \trim($part)));

						if (\count($sections) > 1)
							{
							$parts[$sections[0]] = $sections[1];
							}
						}
					$fileName = \str_replace(' ', '_', $parts['filename']);
					$fileName = \preg_replace('/[^a-zA-Z0-9\.\-\_()]/', '', $fileName);
					$ext = \substr($fileName, \strrpos($fileName, '.'));
					$forumAttachment = new \App\Record\ForumAttachment();
					$forumAttachment->forum = $forum;
					$forumAttachment->forumMessageId = $forumMessageId;
					$forumAttachment->fileName = $fileName;
					$attachmentId = $forumAttachment->insert();
					$fullFileName = $fileModel->getPath() . $attachmentId . $ext;
					$mimePart->saveContent($fullFileName);
					$email->addAttachment($fullFileName, $fileName);
					}
				}
			}
		}
	}
