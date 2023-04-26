<?php

namespace App\Record;

/**
 * @inheritDoc
 *
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\ForumAttachment> $ForumAttachmentChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\ForumMember> $ForumMemberChildren
 * @property \PHPFUI\ORM\RecordCursor<\App\Record\ForumMessage> $ForumMessageChildren
 */
class Forum extends \App\Record\Definition\Forum
	{
	protected static array $virtualFields = [
		'ForumMessageChildren' => [\PHPFUI\ORM\Children::class, \App\Table\ForumMessage::class],
		'ForumMemberChildren' => [\PHPFUI\ORM\Children::class, \App\Table\ForumMember::class],
		'ForumAttachmentChildren' => [\PHPFUI\ORM\Children::class, \App\Table\ForumAttachment::class],
	];

	private static ?\App\Model\ForumAttachmentFiles $fileModel = null;

	public function delete() : bool
		{
		$condition = new \PHPFUI\ORM\Condition('forumId', $this->forumId);

		$forumMemberTable = new \App\Table\ForumMember();
		$forumMemberTable->setWhere($condition);
		$forumMemberTable->delete();

		$forumMessageTable = new \App\Table\ForumMessage();
		$forumMessageTable->setWhere($condition);
		$forumMessageTable->delete();

		$model = self::$fileModel ?: self::$fileModel = new \App\Model\ForumAttachmentFiles();

		foreach ($this->ForumAttachmentChildren as $attachment)
			{
			$model->delete((string)$attachment->forumAttachmentId);
			}
		$forumAttachmentTable = new \App\Table\ForumAttachment();
		$forumAttachmentTable->setWhere($condition);
		$forumAttachmentTable->delete();

		return parent::delete();
		}

	public function clean() : static
		{
		$this->email = \strtolower(\str_replace(' ', '', $this->email));
		$this->description = \App\Tools\TextHelper::cleanUserHtml($this->description);

		return $this;
		}

	public function insert() : bool
		{
		$errors = $this->validate();

		if ($errors)
			{
			\App\Model\Session::setFlash('alert', $errors);

			return false;
			}

		return parent::insert();
		}

	public function update() : bool
		{
		$errors = $this->validate();

		if ($errors)
			{
			\App\Model\Session::setFlash('alert', $errors);

			return false;
			}
		$forum = new \App\Record\Forum($this->forumId);

		if (! $forum->empty())
			{
			if ($forum->name != $this->name)
				{
				$permissionTable = new \App\Table\Permission();
				$permissionTable->setWhere(new \PHPFUI\ORM\Condition('name', $forum->name . '%', new \PHPFUI\ORM\Operator\Like()));

				foreach ($permissionTable->getRecordCursor() as $record)
					{
					$record->name = \str_replace($forum->name, $this->name, (string)$record->name);
					$record->update();
					}
				$permissions = new \App\Model\Permission();
				$permissions->generatePermissionLoader();
				}
			}

		return parent::update();
		}
	}
