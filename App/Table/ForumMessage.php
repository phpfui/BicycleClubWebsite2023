<?php

namespace App\Table;

class ForumMessage extends \PHPFUI\ORM\Table
	{
	protected static string $className = '\\' . \App\Record\ForumMessage::class;

	public function getNextMessage(\App\Record\ForumMessage $message) : \App\Record\ForumMessage
		{
		$sql = $this->getSelectedFields() . ' where fm.forumId=? and fm.forumMessageId>? limit 1';
		$input = [$message->forumId, $message->forumMessageId];

		$forumMessage = new \App\Record\ForumMessage();
		$forumMessage->loadFromSQL($sql, $input);

		return $forumMessage;
		}

	public function getPreviousMessage(\App\Record\ForumMessage $message) : \App\Record\ForumMessage
		{
		$sql = $this->getSelectedFields() . ' where fm.forumId=? and fm.forumMessageId<? order by fm.forumMessageId desc limit 1';
		$input = [$message->forumId, $message->forumMessageId];

		$forumMessage = new \App\Record\ForumMessage();
		$forumMessage->loadFromSQL($sql, $input);

		return $forumMessage;
		}

	private function getSelectedFields() : string
		{
		return 'select * from forumMessage fm left join forum f on f.forumId=fm.forumId ';
		}
	}
