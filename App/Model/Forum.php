<?php

namespace App\Model;

class Forum
	{
	protected ?\App\Tools\EMail $email = null;

	public function __destruct()
		{
		if ($this->email)
			{
			$this->email->bulkSend();
			}
		}

	public function download(\App\Record\ForumAttachment $attachment) : string
		{
		if ($attachment->loaded())
			{
			$downloadName = $attachment->fileName;
			$extension = '';
			$extensionIndex = \strrpos($downloadName, '.');

			if ($extensionIndex)
				{
				$extension = \substr($downloadName, $extensionIndex);
				}
			$fileModel = new \App\Model\ForumAttachmentFiles();
			$error = $fileModel->download($attachment->forumAttachmentId, $extension, $downloadName);

			if ($error)
				{
				\http_response_code(404);

				return "File not found: {$error}";
				}
			}
		else
			{
			\http_response_code(404);

			return "{$attachment->forumAttachmentId} is not a valid attachment number.";
			}

		return '';
		}

	public function getEmail() : ?\App\Tools\EMail
		{
		return $this->email;
		}

	/**
	 * @param array<string,mixed> $fields
	 */
	public function getMembers(\App\Record\Forum $forum, array $fields) : \PHPFUI\ORM\DataObjectCursor
		{
		if (isset($fields['email']))
			{
			$fields['email'] = \App\Model\Member::cleanEmail($fields['email']);
			}

		$forumMemberTable = new \App\Table\ForumMember();

		return $forumMemberTable->getMembers($forum, $fields);
		}

	/**
	 * @return array<string,mixed>
	 */
	public function isAdditionalEmail(string $from, \App\Record\Forum $forum) : array
		{
		$additional = new \App\Record\AdditionalEmail(['email' => \App\Model\Member::cleanEmail($from), 'verified' => 1]);

		if (! $additional->empty())
			{
			$member = $additional->member;

			if ($member->loaded())
				{
				return $member->toArray();
				}
			}

		// could be in white list
		$whiteList = \explode("\n", \str_replace(',', "\n", $forum->whiteList ?? ''));
		$member = [];

		foreach ($whiteList as $email)
			{
			if ($email == $from)
				{
				$member['email'] = $email;
				$member['memberId'] = 0;
				[$member['firstName'], $member['lastName']] = \explode('@', $email);

				break;
				}
			}

		return $member;
		}

	/**
	 * @param array<string,mixed> $message
	 */
	public function post(array $message) : int
		{
		$forum = new \App\Record\Forum((int)$message['forumId']);
		$forumMemberTable = new \App\Table\ForumMember();
		$members = $forumMemberTable->getEmailMembers($forum);

		if (! $forum->loaded() || $forum->closed)
			{
			return 0;	// not found, closed or no members
			}
		$title = $message['title'];
		$settingTable = new \App\Table\Setting();
		$clubAbbrev = $settingTable->value('clubAbbrev');
		$forumFullName = '[' . $clubAbbrev . ' ' . $forum->name . ']';

		if (! \str_contains((string)$title, $forumFullName))
			{
			$dbTitle = $title;
			$emailTitle = $forumFullName . ' ' . $title;
			}
		else
			{
			$dbTitle = \str_replace($forumFullName, '', (string)$title);
			$dbTitle = \str_replace('  ', ' ', $dbTitle);
			$emailTitle = $title;
			}
		$message['title'] = $dbTitle;
		$this->email = new \App\Tools\EMail();

		if (! empty($message['htmlMessage']))
			{
			$html = $message['htmlMessage'];
			$headEnd = \stripos((string)$html, '</head>');

			if (false != $headEnd)
				{
				$html = \substr((string)$html, $headEnd + 7);
				}
			$this->email->setBody($html);
			$this->email->setHtml();

			if (empty($message['textMessage']))
				{
				$message['textMessage'] = \Soundasleep\Html2Text::convert($message['htmlMessage'], ['drop_links' => 'href', 'ignore_errors' => true]);
				}
			}
		else
			{
			$this->email->setBody($message['textMessage']);
			$message['htmlMessage'] = \str_replace("\n", '<br>', (string)$message['textMessage']);
			}
		$forumMessage = new \App\Record\ForumMessage();
		$forumMessage->setFrom($message);
		$insertedId = $forumMessage->insert();
		$forumEmail = $forum['email'] . '@' . \emailServerName();
		$this->email->setSubject($emailTitle);
		$this->email->setFromMember($message);	// message has the needed fields
		$this->email->setReplyTo($forumEmail, $forum->name);

		foreach ($members as $member)
			{
			$this->email->addBCCMember($member->toArray());
			}

		return $insertedId;
		}
	}
