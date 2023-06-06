<?php

namespace App\Cron\Job;

class ForumJournal extends \App\Cron\BaseJob
	{
	public function getDescription() : string
		{
		return 'Email the appropriate Forum journals to all subscribing members.';
		}

	/** @param array<string, string> $parameters */
	public function run(array $parameters = []) : void
		{
		$forumAttachmentTable = new \App\Table\ForumAttachment();
		$forumMessageTable = new \App\Table\ForumMessage();
		$view = new \App\View\Forum(new \App\View\Page(new \App\Model\Controller(new \App\Model\Permission())));
		$setingTable = new \App\Table\Setting();
		$clubAbbrev = $setingTable->value('clubAbbrev');
		$site = $this->controller->getSchemeHost();
		$start = \App\Tools\Date::toString($this->controller->runningAtJD() - 1);
		$forumTable = new \App\Table\Forum();
		$forumTable->addOrderBy('name');

		$condition = new \PHPFUI\ORM\Condition('posted', $start, new \PHPFUI\ORM\Operator\GreaterThanEqual());
		$condition->and('posted', $start . ' 23:59:59', new \PHPFUI\ORM\Operator\LessThanEqual());

		foreach ($forumTable->getRecordCursor() as $forum)
			{
			$forumCondition = clone $condition;
			$forumCondition->and('forumId', $forum->forumId);
			$forumMessageTable->setWhere($forumCondition);
			$messages = $forumMessageTable->getRecordCursor();

			if (\count($messages))
				{
				$forumMemberTable = new \App\Table\ForumMember();
				$members = $forumMemberTable->getDigestMembers($forum);
				$email = new \App\Tools\EMail();
				$title = "{$clubAbbrev} {$forum->name} Daily Digest";
				$email->setSubject($title);
				$email->setFrom($forum->email . '@' . $_SERVER['SERVER_NAME'], $clubAbbrev . ' ' . $forum->name);
				$body = new \PHPFUI\Container();
				$body->add(new \PHPFUI\Header($title));
				$ol = new \PHPFUI\OrderedList();
				$index = 0;

				foreach ($messages as $message)
					{
					++$index;
					$item = new \PHPFUI\ListItem("<a href='#{$index}'>{$message->title}</a>");
					$item->addAttribute('style', 'font-size:1.5em');
					$ol->addItem($item);
					}
				$body->add($ol);
				$body->add("This email was sent you from <a href='{$site}'>{$site}</a>.  You can change your preferences <a href='{$site}/Forums/home/{$forum->forumId}'>here</a>.");
				$index = 0;

				foreach ($messages as $message)
					{
					$body->add('<hr>');
					++$index;
					$body->add("<a name='{$index}'></a>");
					$body->add($view->getPost($message, 500, false));
					$table = new \PHPFUI\Table();
					$replyAllButton = new \PHPFUI\EMailButton('Reply All', "{$site}/Forums/reply/{$message->forumId}/{$message->forumMessageId}");
					$title = \urlencode((string)$message->title);
					$replyPosterButton = new \PHPFUI\EMailButton('Reply To Poster', "{$site}/Membership/email/{$message->memberId}?title={$title}");
					$row = [$replyAllButton, $replyPosterButton];
					$forumAttachmentTable->setWhere(new \PHPFUI\ORM\Condition('forumMessageId', $message->forumMessageId));
					$attachments = \count($forumAttachmentTable);

					if ($attachments)
						{
						$text = $attachments . ' Attachment';

						if ($attachments > 1)
							{
							$text .= 's';
							}
						$row[] = new \PHPFUI\EMailButton($text, "{$site}/Forums/attachments/{$message->forumId}/{$message->forumMessageId}");
						}
					$table->addRow($row);
					$body->add($table);
					}
				$email->setBody("{$body}");
				$email->setHtml();

				foreach ($members as $member)
					{
					$email->addBCCMember($member->toArray());
					}
				$email->bulkSend();
				}
			}
		}

	public function willRun() : bool
		{
		return $this->controller->runAt(0, 15);
		}
	}
